<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Desativa foreign keys temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Cria o admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@onfly.com',
            'password' => Hash::make('admin123'),
            'is_admin' => true,
        ]);
    }
}
