<?php

namespace Tests\Feature;

use App\Services\Backup\DatabaseDumper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseBackupCommandTest extends TestCase
{
    use RefreshDatabase;

    private array $filesToClean = [];

    protected function tearDown(): void
    {
        foreach ($this->filesToClean as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_it_generates_database_backup_with_completion_marker()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_db_bkp_') . '.sql';
        $this->filesToClean[] = $tempFile;

        $this->artisan('db:backup', ['--output' => $tempFile])
            ->assertExitCode(0);

        $this->assertFileExists($tempFile);
        $contents = file_get_contents($tempFile);
        $this->assertNotEmpty($contents);
        $this->assertStringContainsString(DatabaseDumper::COMPLETION_MARKER, $contents);
    }

    public function test_it_creates_default_backup_in_backups_directory()
    {
        $this->artisan('db:backup')
            ->assertExitCode(0);

        $backupsDir = base_path('backups');
        $this->assertDirectoryExists($backupsDir);

        $files = glob($backupsDir . '/microhelium_backup_*.sql');
        $this->assertNotEmpty($files);

        $latestFile = end($files);
        $this->filesToClean[] = $latestFile;

        $contents = file_get_contents($latestFile);
        $this->assertStringContainsString(DatabaseDumper::COMPLETION_MARKER, $contents);
    }

    public function test_it_supports_gzip_compression()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_db_bkp_') . '.sql';
        $gzFile = $tempFile . '.gz';
        $this->filesToClean[] = $tempFile;
        $this->filesToClean[] = $gzFile;

        $this->artisan('db:backup', ['--output' => $tempFile, '--gzip' => true])
            ->assertExitCode(0);

        $this->assertFileDoesNotExist($tempFile);
        $this->assertFileExists($gzFile);

        $decompressed = gzdecode(file_get_contents($gzFile));
        $this->assertNotEmpty($decompressed);
        $this->assertStringContainsString(DatabaseDumper::COMPLETION_MARKER, $decompressed);
    }
}
