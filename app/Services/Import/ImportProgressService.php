<?php

namespace App\Services\Import;

use App\ImportJob;
use App\ImportJobDetail;
use App\ImportJobLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportProgressService
{
    public function initializeJob(ImportJob $job, array $order, array $parsedTables)
    {
        $totalRows = 0;
        foreach ($order as $index => $tableName) {
            $rowCount = 0;
            if (isset($parsedTables[$tableName])) {
                $rowCount = isset($parsedTables[$tableName]['row_count'])
                    ? (int) $parsedTables[$tableName]['row_count']
                    : count($parsedTables[$tableName]['rows']);
            }
            $totalRows += $rowCount;

            ImportJobDetail::updateOrCreate(
                [
                    'import_job_id' => $job->id,
                    'table_name' => $tableName,
                ],
                [
                    'company_id' => $job->company_id,
                    'sort_order' => $index + 1,
                    'status' => 'queued',
                    'total_rows' => $rowCount,
                    'processed_rows' => 0,
                    'failed_rows' => 0,
                    'deferred_rows' => 0,
                    'retries' => 0,
                    'meta' => [
                        'columns' => isset($parsedTables[$tableName]) ? $parsedTables[$tableName]['columns'] : [],
                    ],
                ]
            );
        }

        $job->update([
            'status' => 'queued',
            'total_tables' => count($order),
            'processed_tables' => 0,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'failed_rows' => 0,
        ]);

        $this->log($job, 'info', 'Importación encolada.', ['tables' => $order, 'total_rows' => $totalRows]);
    }

    public function startJob(ImportJob $job)
    {
        if ($job->started_at === null) {
            $job->update([
                'status' => 'running',
                'started_at' => Carbon::now(),
            ]);
        } else {
            $job->update(['status' => 'running']);
        }
    }

    public function startTable(ImportJobDetail $detail)
    {
        $detail->update([
            'status' => 'running',
            'started_at' => $detail->started_at ?: Carbon::now(),
        ]);
    }

    public function incrementTable(ImportJobDetail $detail, $processedRows, $failedRows, $deferredRows)
    {
        if ($processedRows > 0) {
            $detail->increment('processed_rows', $processedRows);
        }

        if ($failedRows > 0) {
            $detail->increment('failed_rows', $failedRows);
        }

        $detail->deferred_rows = $deferredRows;
        $detail->save();
        $this->refreshJob($detail->importJob);
    }

    public function completeTable(ImportJobDetail $detail, $status, array $meta = [], $errorMessage = null)
    {
        $currentMeta = is_array($detail->meta) ? $detail->meta : [];
        $detail->update([
            'status' => $status,
            'finished_at' => Carbon::now(),
            'error_message' => $errorMessage,
            'meta' => array_merge($currentMeta, $meta),
        ]);

        $this->refreshJob($detail->importJob);
    }

    public function completeJob(ImportJob $job, $status, $lastError = null)
    {
        $this->refreshJob($job);
        $job->update([
            'status' => $status,
            'last_error' => $lastError,
            'finished_at' => Carbon::now(),
        ]);
    }

    public function cancelJob(ImportJob $job, $reason = 'Cancelado por usuario.')
    {
        $job->details()
            ->whereIn('status', ['queued', 'running'])
            ->update([
                'status' => 'cancelled',
                'error_message' => $reason,
                'deferred_rows' => 0,
                'finished_at' => Carbon::now(),
            ]);

        $this->refreshJob($job);
        $job->update([
            'status' => 'cancelled',
            'last_error' => $reason,
            'finished_at' => Carbon::now(),
        ]);
    }

    public function failJob(ImportJob $job, $message)
    {
        $job->increment('retries');
        $job->update([
            'status' => 'failed',
            'last_error' => $message,
            'finished_at' => Carbon::now(),
        ]);

        $this->log($job, 'error', 'La importación falló.', ['error' => $message]);
    }

    public function log(ImportJob $job, $level, $message, array $context = [], ImportJobDetail $detail = null)
    {
        $logMessage = "[Import Job #{$job->id}]" . ($detail ? " [Table: {$detail->table_name}]" : "") . " {$message}";
        
        switch ($level) {
            case 'error':
                Log::error($logMessage, $context);
                break;
            case 'warning':
                Log::warning($logMessage, $context);
                break;
            case 'info':
            default:
                Log::info($logMessage, $context);
                break;
        }

        return ImportJobLog::create([
            'import_job_id' => $job->id,
            'import_job_detail_id' => $detail ? $detail->id : null,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public function refreshJob(ImportJob $job)
    {
        $job->load('details');

        $processedTables = $job->details->filter(function ($detail) {
            return in_array($detail->status, ['completed', 'partial', 'failed', 'cancelled'], true);
        })->count();

        $processedRows = $job->details->sum('processed_rows');
        $failedRows = $job->details->sum('failed_rows');

        $job->update([
            'processed_tables' => $processedTables,
            'processed_rows' => $processedRows,
            'failed_rows' => $failedRows,
        ]);
    }
}