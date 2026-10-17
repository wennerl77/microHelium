<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupFailedException;
use App\Services\Backup\DatabaseDumper;
use Illuminate\Console\Command;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup
                            {--output= : Caminho do arquivo de saída .sql}
                            {--connection= : Conexão do banco de dados a ser exportada}
                            {--gzip : Comprimir o dump resultante com gzip (.sql.gz)}';

    protected $description = 'Gera um backup completo do banco de dados (.sql)';

    public function handle(DatabaseDumper $dumper): int
    {
        $connection = $this->option('connection') ?: (string) config('database.default');

        $outputPath = $this->resolveOutputPath();

        $this->info("Iniciando dump do banco de dados (conexão: '{$connection}')...");

        try {
            $dumper->dump($connection, $outputPath);

            if ($this->option('gzip')) {
                $outputPath = $this->compressGzip($outputPath);
            }
        } catch (BackupFailedException $e) {
            $this->error("Falha ao gerar backup: " . $e->getMessage());

            return self::FAILURE;
        }

        $sizeFormatted = $this->formatBytes(filesize($outputPath));
        $relativeDisplay = str_replace(base_path() . '/', '', $outputPath);

        $this->info("✅ Backup concluído com sucesso!");
        $this->line("Arquivo: {$outputPath} ({$sizeFormatted})");
        $this->comment("Para restaurar: make restore-db FILE={$relativeDisplay}");

        return self::SUCCESS;
    }

    private function resolveOutputPath(): string
    {
        if ($output = $this->option('output')) {
            $path = str_starts_with($output, DIRECTORY_SEPARATOR) ? $output : base_path($output);
        } else {
            $filename = 'microhelium_backup_' . now()->format('Y-m-d_His') . '.sql';
            $path = base_path('backups/' . $filename);
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $path;
    }

    private function compressGzip(string $sourcePath): string
    {
        $gzPath = $sourcePath . '.gz';
        $this->line("Comprimindo arquivo com gzip...");

        $source = fopen($sourcePath, 'rb');
        $dest = gzopen($gzPath, 'wb9');

        if ($source === false || $dest === false) {
            throw new BackupFailedException("Não foi possível inicializar a compressão gzip de {$sourcePath}.");
        }

        while (! feof($source)) {
            gzwrite($dest, fread($source, 1024 * 512));
        }

        fclose($source);
        gzclose($dest);
        @unlink($sourcePath);

        return $gzPath;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2, ',', '.') . ' ' . $units[$power];
    }
}
