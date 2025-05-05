<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        
        // Assign admin role to admin user
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminUser->assignRole($adminRole);
        }
        
        // Create project manager user
        $pmUser = User::create([
            'name' => 'Project Manager',
            'email' => 'pm@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        
        // Assign project_manager role
        $pmRole = Role::where('name', 'project_manager')->first();
        if ($pmRole) {
            $pmUser->assignRole($pmRole);
        }
        
        // Create developer users
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => 'Developer ' . $i,
                'email' => 'dev' . $i . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            // Assign developer role
            $devRole = Role::where('name', 'developer')->first();
            if ($devRole) {
                $user->assignRole($devRole);
            }
        }
        
        // Create tester users
        for ($i = 1; $i <= 5; $i++) {
            $user = User::create([
                'name' => 'Tester ' . $i,
                'email' => 'tester' . $i . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            
            // Assign tester role
            $testerRole = Role::where('name', 'tester')->first();
            if ($testerRole) {
                $user->assignRole($testerRole);
            }
        }
        
        // Create a few inactive users for testing
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => 'Inactive User ' . $i,
                'email' => 'inactive' . $i . '@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => false,
            ]);
        }
    }
}