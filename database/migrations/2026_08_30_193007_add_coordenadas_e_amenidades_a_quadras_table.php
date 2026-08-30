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
            $table->decimal('latitude', 10, 7)->nullable()->after('cep');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->json('amenidades')->nullable()->after('cobertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quadras', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'amenidades']);
        });
    }
};
