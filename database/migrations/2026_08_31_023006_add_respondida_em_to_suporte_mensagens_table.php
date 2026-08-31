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
        Schema::table('suporte_mensagens', function (Blueprint $table) {
            $table->timestamp('respondida_em')->nullable()->after('mensagem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suporte_mensagens', function (Blueprint $table) {
            $table->dropColumn('respondida_em');
        });
    }
};
