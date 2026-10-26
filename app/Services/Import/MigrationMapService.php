<?php

namespace App\Services\Import;

use App\MigrationMap;
use Illuminate\Support\Facades\DB;

class MigrationMapService
{
    protected $cache = [];
    protected $warmedUp = [];
    protected $batchBuffer = [];

    public function __destruct()
    {
        $this->flushBuffer();
    }

    public function warmUp($companyId)
    {
        if (isset($this->warmedUp[$companyId])) {
            return;
        }

        try {
            $rows = DB::table('migration_map')
                ->where('company_id', $companyId)
                ->select('import_job_id', 'company_id', 'table_name', 'old_id', 'new_id')
                ->get();

            foreach ($rows as $r) {
                $this->cache["{$r->company_id}_{$r->table_name}_{$r->old_id}"] = $r->new_id;
                if ($r->import_job_id) {
                    $this->cache["{$r->import_job_id}_{$r->company_id}_{$r->table_name}_{$r->old_id}"] = $r->new_id;
                }
            }

            $this->warmedUp[$companyId] = true;
        } catch (\Throwable $e) {
            try {
                DB::purge('pgsql');
                DB::reconnect();
                $rows = DB::table('migration_map')
                    ->where('company_id', $companyId)
                    ->select('import_job_id', 'company_id', 'table_name', 'old_id', 'new_id')
                    ->get();
                foreach ($rows as $r) {
                    $this->cache["{$r->company_id}_{$r->table_name}_{$r->old_id}"] = $r->new_id;
                    if ($r->import_job_id) {
                        $this->cache["{$r->import_job_id}_{$r->company_id}_{$r->table_name}_{$r->old_id}"] = $r->new_id;
                    }
                }
                $this->warmedUp[$companyId] = true;
            } catch (\Throwable $ex) {}
        }
    }

    public function remember($importJobId, $companyId, $tableName, $oldId, $newId, array $sourcePayload = [])
    {
        if ($oldId === null || $oldId === '') {
            return null;
        }

        $this->cache["{$companyId}_{$tableName}_{$oldId}"] = $newId;
        if ($importJobId !== null) {
            $this->cache["{$importJobId}_{$companyId}_{$tableName}_{$oldId}"] = $newId;
        }

        $this->batchBuffer[] = [
            'import_job_id' => $importJobId,
            'company_id'    => $companyId,
            'table_name'    => $tableName,
            'old_id'        => (string) $oldId,
            'new_id'        => $newId,
            'source_payload'=> !empty($sourcePayload) ? json_encode($sourcePayload) : null,
        ];

        if (count($this->batchBuffer) >= 50) {
            $this->flushBuffer();
        }
    }

    public function flushBuffer()
    {
        if (empty($this->batchBuffer)) {
            return;
        }

        $items = $this->batchBuffer;
        $this->batchBuffer = [];

        $attempts = 0;
        while ($attempts < 5) {
            try {
                $valuesSql = [];
                $bindings = [];
                foreach ($items as $item) {
                    $valuesSql[] = "(?, ?, ?, ?, ?, ?, now(), now())";
                    $bindings[] = $item['import_job_id'];
                    $bindings[] = $item['company_id'];
                    $bindings[] = $item['table_name'];
                    $bindings[] = $item['old_id'];
                    $bindings[] = $item['new_id'];
                    $bindings[] = $item['source_payload'];
                }

                $sql = "INSERT INTO migration_map (import_job_id, company_id, table_name, old_id, new_id, source_payload, created_at, updated_at) "
                     . "VALUES " . implode(", ", $valuesSql) . " "
                     . "ON CONFLICT (import_job_id, company_id, table_name, old_id) "
                     . "DO UPDATE SET new_id = EXCLUDED.new_id, source_payload = EXCLUDED.source_payload, updated_at = now()";

                DB::statement($sql, $bindings);
                return;
            } catch (\Throwable $e) {
                $attempts++;
                if ($attempts >= 5) {
                    throw $e;
                }
                try {
                    DB::purge('pgsql');
                    DB::reconnect();
                } catch (\Throwable $ignore) {}
                usleep(300000);
            }
        }
    }

    public function findMappedId($importJobId, $companyId, $tableName, $oldId)
    {
        if ($oldId === null || $oldId === '') {
            return null;
        }

        if ($importJobId !== null && isset($this->cache["{$importJobId}_{$companyId}_{$tableName}_{$oldId}"])) {
            return $this->cache["{$importJobId}_{$companyId}_{$tableName}_{$oldId}"];
        }

        if (isset($this->cache["{$companyId}_{$tableName}_{$oldId}"])) {
            return $this->cache["{$companyId}_{$tableName}_{$oldId}"];
        }

        if (!empty($this->warmedUp[$companyId])) {
            return null;
        }

        $attempts = 0;
        while ($attempts < 5) {
            try {
                $query = MigrationMap::where('company_id', $companyId)
                    ->where('table_name', $tableName)
                    ->where('old_id', (string) $oldId)
                    ->orderByDesc('id');

                if ($importJobId !== null) {
                    $jobSpecific = (clone $query)->where('import_job_id', $importJobId)->value('new_id');
                    if ($jobSpecific !== null) {
                        $this->cache["{$importJobId}_{$companyId}_{$tableName}_{$oldId}"] = $jobSpecific;
                        $this->cache["{$companyId}_{$tableName}_{$oldId}"] = $jobSpecific;
                        return $jobSpecific;
                    }
                }

                $val = $query->value('new_id');
                if ($val !== null) {
                    $this->cache["{$companyId}_{$tableName}_{$oldId}"] = $val;
                }
                return $val;
            } catch (\Throwable $e) {
                $attempts++;
                if ($attempts >= 5) {
                    throw $e;
                }
                try {
                    DB::purge('pgsql');
                    DB::reconnect();
                } catch (\Throwable $ignore) {}
                usleep(300000);
            }
        }
    }
}