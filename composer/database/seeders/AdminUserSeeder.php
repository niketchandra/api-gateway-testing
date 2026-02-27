<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password_hash' => Hash::make('Atglance@123'),
            'dob' => '1990-01-01',
        ]);

        $this->command->info('Default admin user created successfully!');
        $this->command->info('Email: admin@admin.com');
        $this->command->info('Password: Atglance@123');
    }
}
