<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorageVerificationTest extends TestCase
{
    private string $databaseFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseFile = tempnam(sys_get_temp_dir(), 'epautas-test-');
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $this->databaseFile]);
        DB::purge('sqlite');
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        unlink($this->databaseFile);
        parent::tearDown();
    }

    public function test_empty_database_is_refused_without_creating_tables(): void
    {
        $this->artisan('storage:verify')->assertFailed();
        $this->assertSame([], DB::select("SELECT name FROM sqlite_master WHERE type='table'"));
    }

    public function test_migrated_database_passes_and_records_survive_reconnection(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        DB::table('users')->insert([
            'nome' => 'Teste', 'email' => 'persistence@example.test',
            'perfil' => 'comprador', 'password' => 'unchanged-hash',
        ]);
        DB::disconnect('sqlite');
        $this->artisan('storage:verify')->assertSuccessful();
        $this->assertSame('unchanged-hash', DB::table('users')->value('password'));
        $this->assertSame(1, DB::table('users')->count());
    }

    public function test_production_refuses_ephemeral_sqlite_even_with_existing_schema(): void
    {
        Artisan::call('migrate', ['--force' => true]);
        $this->app->instance('env', 'production');
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        $this->artisan('storage:verify')->assertFailed();
    }
}
