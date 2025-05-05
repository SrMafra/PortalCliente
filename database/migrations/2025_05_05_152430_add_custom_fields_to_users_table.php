<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('documento', 20)->nullable()->comment('CPF/CNPJ do cliente');
            $table->string('telefone', 20)->nullable();
            $table->string('empresa_id', 20)->nullable()->comment('Código da empresa no ERP');
            $table->string('cliente_id', 20)->nullable()->comment('Código do cliente no ERP');
            $table->enum('tipo', ['cliente', 'admin', 'vendedor'])->default('cliente');
            $table->boolean('ativo')->default(true);
            $table->timestamp('ultimo_login')->nullable();
            
            // Índices
            $table->index('documento');
            $table->index('cliente_id');
            $table->index('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'documento',
                'telefone',
                'empresa_id',
                'cliente_id',
                'tipo',
                'ativo',
                'ultimo_login'
            ]);
            
            $table->dropIndex(['documento']);
            $table->dropIndex(['cliente_id']);
            $table->dropIndex(['empresa_id']);
        });
    }
};