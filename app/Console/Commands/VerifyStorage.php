<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class VerifyStorage extends Command
{
    protected $signature = 'storage:verify';

    protected $description = 'Verify runtime persistence and schema without modifying data';

    public function handle(): int
    {
        try {
            if (app()->environment('production') && ! config('app.key')) {
                throw new RuntimeException('APP_KEY ausente; preserve a chave do ambiente existente.');
            }

            $connection = DB::connection();
            $driver = $connection->getDriverName();
            if (app()->environment('production')) {
                if (! in_array($driver, ['pgsql', 'sqlite'], true)) {
                    throw new RuntimeException('Produção requer PostgreSQL ou SQLite em disco persistente.');
                }
                if ($driver === 'sqlite') {
                    $path = realpath($connection->getConfig('database'));
                    $mounts = file('/proc/self/mountinfo', FILE_IGNORE_NEW_LINES) ?: [];
                    $mounted = collect($mounts)->contains(function ($line) {
                        return (explode(' ', $line)[4] ?? '') === '/var/data';
                    });
                    if (! $mounted || ! $path || ! str_starts_with($path, '/var/data/') || ! is_writable($path) || ! is_writable(dirname($path))) {
                        throw new RuntimeException('SQLite requer disco montado em /var/data e arquivo existente gravável dentro dele. Restaure o backup antes de iniciar.');
                    }
                }
            }

            $connection->getPdo();
            if ($driver === 'sqlite') {
                if ((int) $connection->selectOne('PRAGMA foreign_keys')->foreign_keys !== 1) {
                    throw new RuntimeException('SQLite requer foreign_keys=ON.');
                }
            }
            foreach (['users', 'pautas', 'compras', 'sessions', 'cache', 'cache_locks', 'migrations'] as $table) {
                if (! Schema::hasTable($table)) {
                    throw new RuntimeException('Schema incompleto. Restaure/migre explicitamente após backup.');
                }
            }
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            if (array_diff(array_keys($files), $migrator->getRepository()->getRan())) {
                throw new RuntimeException('Migrations pendentes. Execute migrate --force após backup e validação.');
            }
            $this->info('Persistência e schema verificados. Isso não substitui backup ou teste de restauração.');

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            // Only our controlled messages: database exceptions may expose credentials.
            if (get_class($e) === RuntimeException::class) {
                $this->error($e->getMessage());
            } else {
                $this->error('Falha ao verificar armazenamento. Confira conexão e schema em ambiente seguro.');
            }
        } catch (Throwable $e) {
            $this->error('Falha ao verificar armazenamento. Confira conexão e schema em ambiente seguro.');
        }

        return self::FAILURE;
    }
}
