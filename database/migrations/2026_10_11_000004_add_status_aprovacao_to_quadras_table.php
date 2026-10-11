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
            // Default "aprovada" para não esconder quadras já cadastradas
            // antes dessa moderação existir; o admin pode suspender quando quiser.
            $table->string('status_aprovacao')->default('aprovada')->after('ativa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quadras', function (Blueprint $table) {
            $table->dropColumn('status_aprovacao');
        });
    }
};
