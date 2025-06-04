<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->text('motivo_rejeicao_chefia')->nullable()->after('aprovado_chefia_por');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas', function (Blueprint $table) {
            //
        });
    }
};
