<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportJobDetail extends Model
{
    protected $fillable = [
        'import_job_id',
        'company_id',
        'table_name',
        'sort_order',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'deferred_rows',
        'retries',
        'started_at',
        'finished_at',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function importJob()
    {
        return $this->belongsTo(ImportJob::class);
    }

    public function logs()
    {
        return $this->hasMany(ImportJobLog::class);
    }
}