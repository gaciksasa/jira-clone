<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $admin = Role::create(['name' => 'admin']);
        $projectManager = Role::create(['name' => 'project_manager']);
        $developer = Role::create(['name' => 'developer']);
        $tester = Role::create(['name' => 'tester']);

        // Create permissions
        $manageUsers = Permission::create(['name' => 'manage users']);
        $manageProjects = Permission::create(['name' => 'manage projects']);
        $manageOwnProjects = Permission::create(['name' => 'manage own projects']);
        $createTask = Permission::create(['name' => 'create task']);
        $editTask = Permission::create(['name' => 'edit task']);
        $deleteTask = Permission::create(['name' => 'delete task']);
        $changeStatus = Permission::create(['name' => 'change status']);

        // Assign permissions to roles
        $admin->givePermissionTo([
            'manage users',
            'manage projects',
            'manage own projects',
            'create task',
            'edit task',
            'delete task',
            'change status',
        ]);

        $projectManager->givePermissionTo([
            'manage own projects',
            'create task',
            'edit task',
            'delete task',
            'change status',
        ]);

        $developer->givePermissionTo([
            'create task',
            'edit task',
            'change status',
        ]);

        $tester->givePermissionTo([
            'create task',
            'edit task',
            'change status',
        ]);
    }
}