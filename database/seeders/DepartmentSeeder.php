<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Engineering', 'code' => 'ENG', 'description' => 'Software development and technical operations'],
            ['name' => 'Marketing', 'code' => 'MKT', 'description' => 'Brand development and promotional activities'],
            ['name' => 'Sales', 'code' => 'SLS', 'description' => 'Business development and customer acquisition'],
            ['name' => 'Finance', 'code' => 'FIN', 'description' => 'Financial planning and accounting'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Recruiting and employee management']
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}