<?php

namespace App\Console\Commands;

use App\Services\Import\DumpSplitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SplitDumpByTable extends Command
{
    protected $splitService;

    protected $signature = 'dump:split-by-table
                            {source? : Ruta absoluta o relativa del dump SQL/TXT}
                            {--output=storage/app/dump_by_table : Carpeta de salida}
                            {--include-empty : Incluir tablas sin registros INSERT}';

    protected $description = 'Divide un dump SQL en un archivo por tabla con conteo de registros';

    public function __construct(DumpSplitService $splitService)
    {
        parent::__construct();
        $this->splitService = $splitService;
    }

    public function handle()
    {
        $sourcePath = $this->resolveSourcePath($this->argument('source'));
        if ($sourcePath === null || !is_file($sourcePath)) {
            $this->error('No se encontro el archivo de origen.');
            return 1;
        }

        $outputPath = $this->resolvePath((string) $this->option('output'));
        try {
            $summary = $this->splitService->split($sourcePath, $outputPath, (bool) $this->option('include-empty'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $this->info('Dump procesado.');
        $this->line('Origen: ' . $summary['source']);
        $this->line('Salida: ' . $outputPath);
        $this->line('Tablas detectadas: ' . $summary['tables_detected']);
        $this->line('Archivos generados: ' . $summary['generated_files']);
        $this->line('Resumen: ' . $summary['summary_path']);

        return 0;
    }

    protected function resolveSourcePath($source)
    {
        if ($source) {
            return $this->resolvePath($source);
        }

        $previewDir = storage_path('app/restore_preview');
        if (!is_dir($previewDir)) {
            return null;
        }

        $files = File::files($previewDir);
        if (empty($files)) {
            return null;
        }

        usort($files, function ($a, $b) {
            return $b->getMTime() <=> $a->getMTime();
        });

        return $files[0]->getPathname();
    }

    protected function resolvePath($path)
    {
        if ($path === '') {
            return base_path();
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) || strpos($path, '/') === 0) {
            return $path;
        }

        return base_path($path);
    }

}
