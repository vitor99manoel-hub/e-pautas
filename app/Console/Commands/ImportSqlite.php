<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ImportSqlite extends Command
{
    protected $signature = 'storage:import-sqlite {source : Absolute path to a verified offline SQLite backup} {--execute : Copy into an empty PostgreSQL schema; otherwise only inspect}';

    protected $description = 'Import an offline SQLite backup into an empty, migrated PostgreSQL database';

    public function handle(): int
    {
        // Parent tables first. Unexpected application tables require a reviewed update.
        $tables = ['users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
            'jobs', 'job_batches', 'failed_jobs', 'pautas', 'compras'];
        $source = null;
        try {
            $path = realpath($this->argument('source'));
            if (! $path || ! is_file($path) || ! is_readable($path)) {
                throw new RuntimeException('Backup SQLite inexistente ou ilegível.');
            }
            $target = DB::connection();
            if ($target->getDriverName() !== 'pgsql') {
                throw new RuntimeException('O destino deve ser PostgreSQL.');
            }
            config(['database.connections.sqlite_import' => [
                'driver' => 'sqlite', 'database' => $path, 'prefix' => '',
                'foreign_key_constraints' => true,
            ]]);
            $source = DB::connection('sqlite_import');
            $source->statement('PRAGMA query_only = ON');
            $source->beginTransaction();
            if ($source->selectOne('PRAGMA integrity_check')->integrity_check !== 'ok'
                || $source->select('PRAGMA foreign_key_check')) {
                throw new RuntimeException('Backup reprovado nos testes de integridade/relações.');
            }
            $names = array_map(fn ($row) => $row->name, $source->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"));
            if (array_diff($names, [...$tables, 'migrations']) || array_diff([...$tables, 'migrations'], $names)) {
                throw new RuntimeException('Tabelas de origem diferentes do schema esperado; revise antes de importar.');
            }
            $migrations = $source->table('migrations')->orderBy('migration')->pluck('migration')->all();
            if ($migrations !== $target->table('migrations')->orderBy('migration')->pluck('migration')->all()) {
                throw new RuntimeException('Migrations de origem e destino divergem; alinhe uma cópia da origem antes de importar.');
            }
            $booleans = [];
            foreach ($tables as $table) {
                $columns = Schema::connection('sqlite_import')->getColumnListing($table);
                $targetColumns = Schema::getColumnListing($table);
                sort($columns);
                sort($targetColumns);
                if ($columns !== $targetColumns || $target->table($table)->exists()) {
                    throw new RuntimeException('Destino deve ter schema equivalente e tabelas vazias.');
                }
                $booleans[$table] = array_column(array_filter(Schema::getColumns($table),
                    fn ($column) => in_array($column['type_name'], ['bool', 'boolean'], true)), 'name');
                $this->line($table.': '.$source->table($table)->count().' registros');
            }
            if (! $this->option('execute')) {
                $this->info('Inspeção concluída; nenhum dado transferido. Use --execute somente após ensaio e backup.');
                return self::SUCCESS;
            }
            $target->transaction(function () use ($target, $source, $tables, $booleans) {
                // Prevent concurrent writers from racing the empty-table check.
                foreach ($tables as $table) {
                    $target->statement('LOCK TABLE "'.$table.'" IN ACCESS EXCLUSIVE MODE');
                    if ($target->table($table)->exists()) {
                        throw new RuntimeException('Destino deixou de estar vazio; importação cancelada.');
                    }
                }
                foreach ($tables as $table) {
                    foreach ($source->table($table)->cursor() as $record) {
                        $row = (array) $record;
                        foreach ($booleans[$table] as $column) {
                            if ($row[$column] !== null) {
                                if (! in_array($row[$column], [0, 1, '0', '1'], true)) {
                                    throw new RuntimeException('Booleano inválido na origem.');
                                }
                                $row[$column] = (bool) $row[$column];
                            }
                        }
                        // Insert raw values: preserve IDs, timestamps and password hashes.
                        $target->table($table)->insert($row);
                    }
                    if ($target->table($table)->count() !== $source->table($table)->count()) {
                        throw new RuntimeException('Contagem divergente; transação cancelada.');
                    }
                    if (Schema::hasColumn($table, 'id') && in_array($table, ['users', 'jobs', 'failed_jobs', 'pautas', 'compras'], true)) {
                        $target->select("SELECT setval(pg_get_serial_sequence(?, 'id'), COALESCE((SELECT MAX(id) FROM \"$table\"), 1), EXISTS(SELECT 1 FROM \"$table\"))", [$table]);
                    }
                }
            });
            $this->info('Importação concluída. Valide valores financeiros e fluxos antes de liberar tráfego.');
            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error(get_class($e) === RuntimeException::class ? $e->getMessage() : 'Falha de importação; registros do destino revertidos. Investigue em ambiente seguro.');
        } catch (Throwable $e) {
            $this->error('Falha de importação; registros do destino revertidos. Investigue em ambiente seguro.');
        } finally {
            if ($source && $source->transactionLevel() > 0) {
                $source->rollBack();
            }
            DB::purge('sqlite_import');
        }
        return self::FAILURE;
    }
}
