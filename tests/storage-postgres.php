<?php

// Disposable CI database only. Never run against production.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message.' '.Artisan::output());
    }
}

check(DB::connection()->getDriverName() === 'pgsql'
    && DB::connection()->getDatabaseName() === 'epautas_test', 'Requires disposable epautas_test PostgreSQL.');
$path = tempnam(sys_get_temp_dir(), 'epautas-import-');
try {
    config(['database.connections.source_fixture' => [
        'driver' => 'sqlite', 'database' => $path, 'prefix' => '', 'foreign_key_constraints' => true,
    ]]);
    check(Artisan::call('migrate', ['--database' => 'source_fixture', '--force' => true]) === 0, 'SQLite migration');
    $source = DB::connection('source_fixture');
    $source->table('users')->insert([
        'id' => 41, 'nome' => 'CI', 'email' => 'storage-ci@example.test',
        'perfil' => 'comprador', 'password' => 'preserved-hash',
    ]);
    $source->table('pautas')->insert([
        'id' => 71, 'user_id' => 41, 'titulo' => 'CI', 'nicho' => 'Teste', 'descricao' => 'Teste',
        'cidade' => 'Palmas', 'estado' => 'TO', 'valor' => '100.10',
        'nome' => 'CI', 'telefone' => '0', 'email' => 'storage-ci@example.test',
        'negociavel' => true, 'vendida' => true, 'relevante' => false, 'status' => 'aprovada',
    ]);
    $source->table('compras')->insert([
        'id' => 91, 'comprador_id' => 41, 'pauta_id' => 71, 'valor_pauta' => '100.10',
        'valor_taxa' => '15.02', 'valor_total' => '115.12', 'status' => 'invalid-for-postgres',
    ]);
    $args = ['source' => $path];
    check(Artisan::call('storage:import-sqlite', $args) === 0, 'Inspection');
    check(DB::table('users')->count() === 0, 'Inspection wrote data');
    check(Artisan::call('storage:import-sqlite', $args + ['--execute' => true]) !== 0, 'Invalid enum accepted');
    check(DB::table('users')->count() === 0 && DB::table('pautas')->count() === 0, 'Failed import was not atomic');
    $source->table('compras')->update(['status' => 'paga']);
    $hash = hash_file('sha256', $path);
    check(Artisan::call('storage:import-sqlite', $args + ['--execute' => true]) === 0, 'Import');
    check(hash_file('sha256', $path) === $hash, 'Import modified source');
    check(Artisan::call('storage:import-sqlite', $args + ['--execute' => true]) !== 0, 'Nonempty destination accepted');
    DB::disconnect();
    check(DB::table('users')->where('id', 41)->value('password') === 'preserved-hash', 'Hash/ID not preserved');
    check(DB::table('compras')->where('id', 91)->value('valor_total') === '115.12', 'Decimal not preserved');
    check(DB::table('pautas')->where('id', 71)->value('vendida') === true, 'Boolean not preserved');
    $id = DB::table('users')->insertGetId([
        'nome' => 'Next ID', 'email' => 'next@example.test', 'perfil' => 'comprador', 'password' => 'test',
    ]);
    check($id > 41, 'Sequence was not reset');
    check(Artisan::call('migrate', ['--force' => true]) === 0, 'Repeated migrate');
    check(DB::table('compras')->count() === 1, 'Repeated migrate lost purchases');
    echo "PostgreSQL: import, rollback, no overwrite, source unchanged, IDs, hashes, booleans, decimals, sequences and reconnect passed.\n";
} finally {
    DB::disconnect('source_fixture');
    unlink($path);
}
