<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update the real admin
        User::updateOrCreate(
            ['email' => 'chzakaria037@gmail.com'],
            [
                'name' => 'Zisco',
                'password' => Hash::make('48Pgvv99'),
                'role' => 'admin',
            ]
        );

        // Demote the other user if they exist
        User::updateOrCreate(
            ['email' => 'chamekhzakaria95@gmail.com'],
            [
                'name' => 'Zakaria Chamekh',
                'password' => '$2y$12$QYl4NTa9/8naJjYgD7tQlO/m2TCT4Ucpb4E5Q4tJShKxrcisjEt4i', // Same hash or a new one
                'role' => 'customer',
            ]
        );
    }
}
