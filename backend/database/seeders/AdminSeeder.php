<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $adminExists = User::where('email', 'admin@munext.com')->exists();

        if (!$adminExists) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@munext.com',
                'password' => Hash::make('abcd1234'), // CHANGE THIS PASSWORD!
                'role' => 'admin',
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@munext.com');
            $this->command->info('Password: abcd1234');
            $this->command->warn('IMPORTANT: Change this password immediately!');
        } else {
            $this->command->info('Admin user already exists. Skipping...');
        }
    }
}
