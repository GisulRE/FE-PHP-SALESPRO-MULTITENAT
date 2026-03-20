<?php

namespace App\Services\Import;

use App\MigrationMap;

class MigrationMapService
{
    public function remember($importJobId, $companyId, $tableName, $oldId, $newId, array $sourcePayload = [])
    {
        if ($oldId === null || $oldId === '') {
            return null;
        }

        return MigrationMap::updateOrCreate(
            [
                'import_job_id' => $importJobId,
                'company_id' => $companyId,
                'table_name' => $tableName,
                'old_id' => (string) $oldId,
            ],
            [
                'new_id' => $newId,
                'source_payload' => $sourcePayload,
            ]
        );
    }

    public function findMappedId($importJobId, $companyId, $tableName, $oldId)
    {
        if ($oldId === null || $oldId === '') {
            return null;
        }

        $query = MigrationMap::where('company_id', $companyId)
            ->where('table_name', $tableName)
            ->where('old_id', (string) $oldId)
            ->orderByDesc('id');

        if ($importJobId !== null) {
            $jobSpecific = (clone $query)->where('import_job_id', $importJobId)->value('new_id');
            if ($jobSpecific !== null) {
                return $jobSpecific;
            }
        }

        return $query->value('new_id');
    }
}