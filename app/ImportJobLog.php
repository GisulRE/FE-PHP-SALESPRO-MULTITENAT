<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportJobLog extends Model
{
    protected $fillable = [
        'import_job_id',
        'import_job_detail_id',
        'level',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function importJob()
    {
        return $this->belongsTo(ImportJob::class);
    }

    public function detail()
    {
        return $this->belongsTo(ImportJobDetail::class, 'import_job_detail_id');
    }
}