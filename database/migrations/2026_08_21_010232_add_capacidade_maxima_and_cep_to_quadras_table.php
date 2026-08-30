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
        Schema::table('quadras', function (Blueprint $table) {
            $table->unsignedInteger('capacidade_maxima')->nullable()->after('valor_hora');
            $table->string('cep', 9)->nullable()->after('cidade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quadras', function (Blueprint $table) {
            $table->dropColumn(['capacidade_maxima', 'cep']);
        });
    }
};
