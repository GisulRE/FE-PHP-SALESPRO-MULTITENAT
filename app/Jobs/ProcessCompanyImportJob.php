<?php

namespace App\Jobs;

use App\ImportJob;
use App\Services\Import\CompanySqlImportService;
use App\Services\Import\ImportProgressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCompanyImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 3600;

    protected $importJobId;

    public function __construct($importJobId)
    {
        $this->importJobId = $importJobId;
    }

    public function handle(CompanySqlImportService $importService)
    {
        $job = ImportJob::findOrFail($this->importJobId);
        $importService->run($job);
    }

    public function failed(\Throwable $exception)
    {
        $job = ImportJob::find($this->importJobId);
        if ($job) {
            app(ImportProgressService::class)->failJob($job, $exception->getMessage());
        }
    }
}