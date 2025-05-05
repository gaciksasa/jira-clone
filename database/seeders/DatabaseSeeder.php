<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            TaskTypeSeeder::class,
            PrioritySeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            TaskSeeder::class,
        ]);
    }
}