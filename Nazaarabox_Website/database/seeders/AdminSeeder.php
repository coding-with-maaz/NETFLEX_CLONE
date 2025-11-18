<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $existingAdmin = Admin::where('email', 'hello@hello.com')
            ->orWhere('name', 'admin')
            ->first();

        if (!$existingAdmin) {
            $hashedPassword = Hash::make('Admin123!@#');
            Admin::create([
                'name' => 'admin',
                'email' => 'admin@nazaarabox.com',
                'password' => $hashedPassword, // Pass already hashed password to avoid double hashing
                'role' => 'super_admin',
                'is_active' => true,
            ]);
        } else {
            // Update password if exists - use setAttribute to bypass setPasswordAttribute
            $hashedPassword = Hash::make('Admin123!@#');
            $existingAdmin->setAttribute('password', $hashedPassword);
            $existingAdmin->is_active = true;
            $existingAdmin->save();
        }

        // Create moderator
        $existingModerator = Admin::where('email', 'moderator@nazaarabox.com')->first();
        if (!$existingModerator) {
            Admin::create([
                'name' => 'Moderator',
                'email' => 'moderator@nazaarabox.com',
                'password' => Hash::make('moderator123'),
                'role' => 'moderator',
                'is_active' => true,
            ]);
        }
    }
}

