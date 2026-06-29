<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('comprador_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('pauta_id')
                ->constrained('pautas')
                ->cascadeOnDelete();

            $table->decimal('valor_pauta', 10, 2);
            $table->decimal('valor_taxa', 10, 2);
            $table->decimal('valor_total', 10, 2);

            $table->string('forma_pagamento')->nullable();
            $table->enum('status', ['pendente', 'paga', 'cancelada'])->default('pendente');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};