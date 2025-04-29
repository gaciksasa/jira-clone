<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskType;

class TaskTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name' => 'Epic',
                'icon' => 'lightning',
                'color' => '#6554c0',
            ],
            [
                'name' => 'Story',
                'icon' => 'book',
                'color' => '#36b37e',
            ],
            [
                'name' => 'Task',
                'icon' => 'check-square',
                'color' => '#4fade6',
            ],
            [
                'name' => 'Bug',
                'icon' => 'alert-triangle',
                'color' => '#e5493a',
            ],
        ];

        foreach ($types as $type) {
            TaskType::create($type);
        }
    }
}