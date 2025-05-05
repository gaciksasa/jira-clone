<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\Department;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users with admin role to use as project leads
        $adminUsers = User::role('admin')->get();
        
        // If no admin users, get users with project_manager role
        if ($adminUsers->isEmpty()) {
            $adminUsers = User::role('project_manager')->get();
        }
        
        // If still no users, get any active users
        if ($adminUsers->isEmpty()) {
            $adminUsers = User::where('is_active', true)->get();
        }
        
        if ($adminUsers->isEmpty()) {
            echo "No active users found. Please run UserSeeder first.\n";
            return;
        }
        
        // Get departments
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            echo "No departments found. Creating projects without department assignments.\n";
        }
        
        // Map departments to codes for easy reference
        $departmentMap = [
            'ENG' => 'Engineering (ENG)',
            'MKT' => 'Marketing (MKT)',
            'SLS' => 'Sales (SLS)',
            'FIN' => 'Finance (FIN)',
            'HR' => 'Human Resources (HR)',
        ];
        
        // Find department IDs by their names
        $departmentIds = [];
        foreach ($departments as $department) {
            foreach ($departmentMap as $code => $name) {
                if (stripos($department->name, explode(' ', $name)[0]) !== false) {
                    $departmentIds[$code] = $department->id;
                    break;
                }
            }
        }
        
        // Sample project data with proper keys and department assignments
        $projects = [
            [
                'name' => 'Website Redesign',
                'key' => 'WEB',
                'description' => 'Redesign the company website with modern UI/UX principles and improved functionality.',
                'department' => 'MKT',
            ],
            [
                'name' => 'E-commerce Platform',
                'key' => 'SHOP',
                'description' => 'Build an e-commerce platform with payment integration and inventory management.',
                'department' => 'SLS',
            ],
            [
                'name' => 'System Maintenance',
                'key' => 'MAINT',
                'description' => 'General system maintenance tasks and bug fixes.',
                'department' => 'ENG',
            ],
            [
                'name' => 'Mobile App Development',
                'key' => 'APP',
                'description' => 'Develop a cross-platform mobile application for both iOS and Android.',
                'department' => 'ENG',
            ],
            [
                'name' => 'CRM Implementation',
                'key' => 'CRM',
                'description' => 'Implement and customize a Customer Relationship Management system.',
                'department' => 'SLS',
            ],
            [
                'name' => 'Data Migration Project',
                'key' => 'DATA',
                'description' => 'Migrate legacy data to the new database structure with data validation and cleaning.',
                'department' => 'ENG',
            ],
            [
                'name' => 'API Integration',
                'key' => 'API',
                'description' => 'Develop and implement API integrations with third-party services.',
                'department' => 'ENG',
            ],
            [
                'name' => 'Security Audit',
                'key' => 'SEC',
                'description' => 'Perform comprehensive security audit and implement necessary improvements.',
                'department' => 'ENG',
            ],
            [
                'name' => 'DevOps Pipeline',
                'key' => 'DEVOP',
                'description' => 'Implement CI/CD pipeline and automate deployment processes.',
                'department' => 'ENG',
            ],
            [
                'name' => 'Business Intelligence Dashboard',
                'key' => 'BI',
                'description' => 'Develop business intelligence dashboards and reporting systems.',
                'department' => 'FIN',
            ],
        ];
        
        // Create projects
        foreach ($projects as $projectData) {
            // Select a random lead
            $leadUser = $adminUsers->random();
            
            // Check if project with this key already exists
            $existingProject = Project::where('key', $projectData['key'])->first();
            
            if (!$existingProject) {
                // Prepare project data
                $projectAttributes = [
                    'name' => $projectData['name'],
                    'key' => $projectData['key'],
                    'description' => $projectData['description'],
                    'lead_id' => $leadUser->id,
                ];
                
                // Add department_id if available
                if (!empty($departmentIds) && isset($projectData['department']) && isset($departmentIds[$projectData['department']])) {
                    $projectAttributes['department_id'] = $departmentIds[$projectData['department']];
                }
                
                // Create the project
                $project = Project::create($projectAttributes);
                
                // Add the lead as a member
                $project->members()->attach($leadUser->id);
                
                // Add 3-8 random members to the project
                $members = User::where('id', '!=', $leadUser->id)
                              ->where('is_active', true)
                              ->inRandomOrder()
                              ->take(rand(3, 8))
                              ->get();
                
                foreach ($members as $member) {
                    $project->members()->attach($member->id);
                }
                
                // Create default board columns (task statuses)
                $defaultStatuses = [
                    ['name' => 'To Do', 'slug' => 'to-do-' . $project->id, 'order' => 1],
                    ['name' => 'In Progress', 'slug' => 'in-progress-' . $project->id, 'order' => 2],
                    ['name' => 'In Review', 'slug' => 'in-review-' . $project->id, 'order' => 3],
                    ['name' => 'Done', 'slug' => 'done-' . $project->id, 'order' => 4],
                ];
                
                foreach ($defaultStatuses as $status) {
                    TaskStatus::create([
                        'name' => $status['name'],
                        'slug' => $status['slug'], 
                        'order' => $status['order'],
                        'project_id' => $project->id,
                    ]);
                }
                
                $departmentName = isset($projectData['department']) && isset($departmentIds[$projectData['department']]) 
                    ? " in department " . $projectData['department'] 
                    : "";
                    
                echo "Created project: " . $projectData['name'] . " (" . $projectData['key'] . ")" . $departmentName . "\n";
            } else {
                // Update existing project with department if not set
                if (!empty($departmentIds) && 
                    isset($projectData['department']) && 
                    isset($departmentIds[$projectData['department']]) && 
                    empty($existingProject->department_id)) {
                    
                    $existingProject->department_id = $departmentIds[$projectData['department']];
                    $existingProject->save();
                    
                    echo "Updated project " . $projectData['key'] . " with department " . $projectData['department'] . "\n";
                } else {
                    echo "Project with key " . $projectData['key'] . " already exists. Skipping.\n";
                }
            }
        }
    }
}