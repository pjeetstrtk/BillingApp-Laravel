<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'billing-backup-test-' . uniqid();
        config(['backup.path' => $this->dir, 'backup.keep' => 2]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::create(['username' => 'admin', 'password' => 'admin123', 'role' => 'Admin']);
    }

    private function fakeBackup(string $name, int $age): void
    {
        File::ensureDirectoryExists($this->dir);
        File::put($this->dir . DIRECTORY_SEPARATOR . $name, '-- dump');
        touch($this->dir . DIRECTORY_SEPARATOR . $name, time() - $age);
    }

    public function test_guests_cannot_see_backups(): void
    {
        $this->get('/backups')->assertRedirect('/login');
    }

    public function test_backups_can_be_listed_downloaded_and_deleted(): void
    {
        $this->actingAs($this->admin());
        $this->fakeBackup('db-20260101-000000.sql', 60);

        $this->get('/backups')->assertOk()->assertSee('db-20260101-000000.sql');
        $this->get('/backups/db-20260101-000000.sql')->assertOk();
        $this->delete('/backups/db-20260101-000000.sql')->assertRedirect(route('backups.index'));

        $this->assertFileDoesNotExist($this->dir . DIRECTORY_SEPARATOR . 'db-20260101-000000.sql');
    }

    public function test_unsafe_or_missing_names_are_not_served(): void
    {
        $this->actingAs($this->admin());

        $this->get('/backups/missing.sql')->assertNotFound();
        $this->get('/backups/secret.txt')->assertNotFound();
        $this->assertNull(app(DatabaseBackup::class)->pathFor('../../.env'));
    }

    public function test_only_the_newest_backups_are_kept(): void
    {
        $this->fakeBackup('db-a.sql', 300);
        $this->fakeBackup('db-b.sql', 200);
        $this->fakeBackup('db-c.sql', 100);

        $this->assertSame(1, app(DatabaseBackup::class)->prune());
        $this->assertSame(['db-c.sql', 'db-b.sql'], app(DatabaseBackup::class)->all()->pluck('name')->all());
    }
}
