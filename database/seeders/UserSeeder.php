<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Criar usuário administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'tipo' => 'admin',
            'ativo' => true,
            'email_verified_at' => now(),
            'empresa_id' => config('erp.empresa_padrao')
        ]);
        
        // Criar usuário demo para cliente
        User::create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@example.com',
            'password' => Hash::make('cliente123'),
            'tipo' => 'cliente',
            'ativo' => true,
            'email_verified_at' => now(),
            'empresa_id' => config('erp.empresa_padrao'),
            'cliente_id' => '12345', // Substituir pelo código real do cliente no ERP
            'documento' => '12345678901234' // Substituir pelo documento real do cliente
        ]);
    }
}