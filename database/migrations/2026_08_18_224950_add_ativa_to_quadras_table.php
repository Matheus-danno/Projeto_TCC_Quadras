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
            $table->boolean('ativa')->default(true)->after('cobertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quadras', function (Blueprint $table) {
            $table->dropColumn('ativa');
        });
    }
};
