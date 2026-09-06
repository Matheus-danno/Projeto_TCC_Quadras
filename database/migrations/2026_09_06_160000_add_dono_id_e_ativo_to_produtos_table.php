<?php

use App\Models\User;
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
        Schema::table('produtos', function (Blueprint $table) {
            $table->foreignId('dono_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->boolean('ativo')->default(true)->after('estoque');
        });

        // Produtos cadastrados antes de a loja ter dono (dados de demonstração)
        // ficam com o primeiro dono de quadra existente, em vez de órfãos.
        $primeiroDono = User::where('role', 'dono_quadra')->orderBy('id')->value('id');

        if ($primeiroDono) {
            DB::table('produtos')->whereNull('dono_id')->update(['dono_id' => $primeiroDono]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dono_id');
            $table->dropColumn('ativo');
        });
    }
};
