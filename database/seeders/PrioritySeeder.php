<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run()
    {
        $priorities = [
            [
                'name' => 'Highest',
                'icon' => 'arrow-up',
                'color' => '#e5493a',
                'order' => 1,
            ],
            [
                'name' => 'High',
                'icon' => 'arrow-up',
                'color' => '#e97f33',
                'order' => 2,
            ],
            [
                'name' => 'Medium',
                'icon' => 'minus',
                'color' => '#e2b203',
                'order' => 3,
            ],
            [
                'name' => 'Low',
                'icon' => 'arrow-down',
                'color' => '#79e2f2',
                'order' => 4,
            ],
            [
                'name' => 'Lowest',
                'icon' => 'arrow-down',
                'color' => '#c8c8c8',
                'order' => 5,
            ],
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}