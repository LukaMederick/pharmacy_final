<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;      // Asegúrate de importar tu modelo de usuario

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'name' => 'Admin User',
            'email' => 'luka@gmail.com',
            'password' => bcrypt('password')  // Se recomienda usar "password" para las pruebas
        ]);
    }
}
