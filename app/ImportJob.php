<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'source_name',
        'source_path',
        'status',
        'total_tables',
        'processed_tables',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'retries',
        'max_retries',
        'started_at',
        'finished_at',
        'last_error',
        'root_tables',
        'migration_order',
        'options',
    ];

    protected $casts = [
        'root_tables' => 'array',
        'migration_order' => 'array',
        'options' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(ImportJobDetail::class)->orderBy('sort_order');
    }

    public function logs()
    {
        return $this->hasMany(ImportJobLog::class)->latest('id');
    }

    public function migrationMaps()
    {
        return $this->hasMany(MigrationMap::class);
    }
}