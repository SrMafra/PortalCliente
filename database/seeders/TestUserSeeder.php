<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Usuário Teste',
            'email' => 'teste@example.com',
            'password' => Hash::make('senha123'),
            'tipo' => 'cliente',
            'documento' => '63770820000182', // CNPJ do cliente da API
            'cliente_id' => '3', // Código do cliente na API
            'empresa_id' => '1',
            'ativo' => true,
            'email_verified_at' => now()
        ]);
        
        $this->command->info('Usuário de teste criado!');
    }
}