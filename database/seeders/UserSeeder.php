<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Zisco',
            'email' => 'chzakaria037@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('48Pgvv99'),
            'role' => 'admin',
        ]);

        \App\Models\User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'customer',
        ]);
    }
}
