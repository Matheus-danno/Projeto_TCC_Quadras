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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf')->nullable()->unique()->after('role');
            $table->date('data_nascimento')->nullable()->after('cpf');
            $table->string('sexo')->nullable()->after('data_nascimento');
            $table->string('endereco')->nullable()->after('sexo');
            $table->string('cep')->nullable()->after('endereco');
            $table->string('cidade')->nullable()->after('cep');
            $table->string('estado', 2)->nullable()->after('cidade');
            $table->string('telefone')->nullable()->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cpf', 'data_nascimento', 'sexo', 'endereco', 'cep', 'cidade', 'estado', 'telefone']);
        });
    }
};
