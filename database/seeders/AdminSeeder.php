<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
{
    \App\Models\User::create([
        'username' => 'admin_safespeak', // Your admin username
        'full_name' => 'System Administrator',
        'password' => \Illuminate\Support\Facades\Hash::make('your_secure_password'), // This hashes the password
        'role' => 'admin',
        'department' => 'IT Department',
    ]);
}
}
