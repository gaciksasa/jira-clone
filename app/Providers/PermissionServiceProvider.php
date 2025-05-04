<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Clear permission cache when application is booting
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Create permissions if they don't exist
        $this->createPermissions();
    }

    protected function createPermissions()
    {
        // Define all necessary permissions
        $permissions = [
            'manage users',
            'manage projects',
            'manage own projects',
            'create task',
            'edit task',
            'delete task',
            'change status'
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}