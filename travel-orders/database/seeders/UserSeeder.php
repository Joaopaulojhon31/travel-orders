<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@admin.com',
            'password' => Hash::make('admin1234'),
            'is_admin' => true,
        ]);

        // Usuário Comum
        User::create([
            'name'     => 'João Silva',
            'email'    => 'joao@email.com',
            'password' => Hash::make('12345678'),
            'is_admin' => false,
        ]);
    }
}
