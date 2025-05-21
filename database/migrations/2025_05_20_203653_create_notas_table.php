<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_nf');
            $table->string('prestador'); // Nome da clínica
            $table->string('cnpj')->nullable(); // Novo campo CNPJ
            $table->decimal('valor_total', 10, 2)->default(0); // Soma dos valores vinculados
            $table->boolean('taxa_correio')->default(false); // Novo campo boolean
            $table->decimal('valor_taxa_correio', 10, 2)->default(0); // Novo campo valor da taxa
            $table->date('data_emissao')->nullable();
            $table->date('vencimento_original')->nullable();
            $table->date('data_entregue_financeiro')->nullable(); // Novo campo data entregue para financeiro
            $table->string('mes')->nullable(); // Ex: "05/2025"
            $table->date('vencimento_prorrogado')->nullable();
            $table->enum('tipo_pagamento', ['boleto', 'deposito', 'pix'])->nullable();
            $table->json('dados_bancarios')->nullable(); // Pode armazenar banco, agência, conta, etc.
            $table->enum('status', ['lancada', 'aprovada_chefia', 'confirmada_financeiro', 'rejeitada'])->default('lancada');
            $table->string('arquivo_nf')->nullable(); // Path do PDF
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quem lançou a nota
            $table->timestamp('aprovado_chefia_em')->nullable();
            $table->foreignId('aprovado_chefia_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmado_financeiro_em')->nullable();
            $table->foreignId('confirmado_financeiro_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
