<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageDirectoryPermissionsTest extends TestCase
{
    public function test_local_disk_creates_directories_accessible_to_other_uids(): void
    {
        $this->assertSame(0775, config('filesystems.disks.local.permissions.dir.private'));
        $this->assertSame(0664, config('filesystems.disks.local.permissions.file.private'));

        $dir = 'perm-test-' . uniqid();
        Storage::disk('local')->put("$dir/a/b/file.txt", 'x');

        $path = Storage::disk('local')->path("$dir/a/b");
        $this->assertSame('0775', sprintf('%04o', fileperms($path) & 0777));

        Storage::disk('local')->deleteDirectory($dir);
    }
}
