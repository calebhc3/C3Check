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
    Schema::create('nota_clientes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('nota_id')->constrained('notas')->onDelete('cascade');
        $table->string('cliente_atendido');
        $table->decimal('valor', 10, 2);
        $table->text('observacao')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_clientes');
    }
};
