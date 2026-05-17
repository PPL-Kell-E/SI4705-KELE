<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@gmail.com')->exists()) {
            User::create([
                'full_name'     => 'Admin',
                'email'         => 'admin@gmail.com',
                'password_hash' => 'admin',
                'role'          => 'admin',
            ]);
        }
    }
}
