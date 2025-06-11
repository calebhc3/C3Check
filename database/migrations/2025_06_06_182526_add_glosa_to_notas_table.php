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
            $table->text('glosa_motivo')->nullable();
            $table->decimal('glosa_valor', 10, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('notas', function (Blueprint $table) {
            $table->dropColumn(['glosa_motivo', 'glosa_valor']);
        });
    }

};
