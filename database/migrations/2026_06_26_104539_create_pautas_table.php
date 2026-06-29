<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pautas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('titulo');
            $table->string('nicho');
            $table->text('descricao');

            $table->string('cidade');
            $table->string('estado', 2);

            $table->string('arquivo')->nullable();

            $table->decimal('valor', 10, 2);
            $table->boolean('negociavel')->default(false);

            $table->string('nome');
            $table->string('telefone');
            $table->string('email');

            $table->boolean('vendida')->default(false);

            $table->enum('status', ['pendente', 'aprovada', 'reprovada'])->default('pendente');
            $table->boolean('relevante')->default(false);
            $table->text('motivo_reprovacao')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pautas');
    }
};