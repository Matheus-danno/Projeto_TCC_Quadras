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
        Schema::table('quadra_fotos', function (Blueprint $table) {
            $table->text('motivo_recusa')->nullable()->after('status_moderacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quadra_fotos', function (Blueprint $table) {
            $table->dropColumn('motivo_recusa');
        });
    }
};
