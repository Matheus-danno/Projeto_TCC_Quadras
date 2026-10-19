<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('cep', 9)->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado', 2)->nullable();
            $table->timestamps();
        });

        $usuarios = DB::table('users')->whereNotNull('endereco')->get(['id', 'endereco', 'cep', 'cidade', 'estado']);

        foreach ($usuarios as $usuario) {
            DB::table('enderecos')->insert([
                'user_id' => $usuario->id,
                'cep' => $usuario->cep,
                'logradouro' => $usuario->endereco,
                'cidade' => $usuario->cidade,
                'estado' => $usuario->estado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['endereco', 'cep', 'cidade', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('endereco')->nullable()->after('sexo');
            $table->string('cep')->nullable()->after('endereco');
            $table->string('cidade')->nullable()->after('cep');
            $table->string('estado', 2)->nullable()->after('cidade');
        });

        foreach (DB::table('enderecos')->get() as $endereco) {
            $logradouro = trim($endereco->logradouro.($endereco->numero ? ', '.$endereco->numero : ''));

            DB::table('users')->where('id', $endereco->user_id)->update([
                'endereco' => $logradouro ?: null,
                'cep' => $endereco->cep,
                'cidade' => $endereco->cidade,
                'estado' => $endereco->estado,
            ]);
        }

        Schema::dropIfExists('enderecos');
    }
};
