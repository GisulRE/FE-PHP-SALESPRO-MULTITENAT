<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MigrationMap extends Model
{
    protected $table = 'migration_map';

    protected $fillable = [
        'import_job_id',
        'company_id',
        'table_name',
        'old_id',
        'new_id',
        'source_payload',
    ];

    protected $casts = [
        'source_payload' => 'array',
    ];

    public function importJob()
    {
        return $this->belongsTo(ImportJob::class);
    }
}