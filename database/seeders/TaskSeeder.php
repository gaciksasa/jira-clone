<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Project;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\Priority;
use App\Models\User;
use App\Models\Sprint;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::with(['members', 'taskStatuses'])->get();
        
        if ($projects->isEmpty()) {
            echo "No projects found. Please run ProjectSeeder first.\n";
            return;
        }
        
        // Get task types and priorities
        $taskTypes = TaskType::all();
        $priorities = Priority::all();
        
        if ($taskTypes->isEmpty() || $priorities->isEmpty()) {
            echo "Task types or priorities not found. Please run TaskTypeSeeder and PrioritySeeder first.\n";
            return;
        }
        
        // Sample task descriptions
        $taskDescriptions = [
            'Implement the user authentication system with email verification.',
            'Design and create the database schema with proper relationships.',
            'Create REST API endpoints for the mobile application.',
            'Fix the responsive design issues on small screens.',
            'Optimize database queries to improve application performance.',
            'Write unit tests for core business logic.',
            'Implement file upload functionality with validation and storage.',
            'Design and implement the dashboard UI with charts and statistics.',
            'Set up continuous integration and deployment pipeline.',
            'Create user documentation with examples and screenshots.',
            'Perform security audit and implement necessary fixes.',
            'Optimize frontend assets and implement lazy loading.',
            'Implement multi-language support for the application.',
            'Create admin panel for user management and reports.',
            'Implement payment gateway integration for subscription plans.',
        ];
        
        foreach ($projects as $project) {
            // Get project members and statuses
            $members = $project->members;
            $statuses = $project->taskStatuses;
            
            if ($members->isEmpty() || $statuses->isEmpty()) {
                continue;
            }
            
            // Determine how many tasks to create for this project (10-25)
            $taskCount = rand(10, 25);
            
            // Create active sprint for the project
            $sprint = Sprint::create([
                'name' => 'Sprint ' . date('Y-m') . '-1',
                'project_id' => $project->id,
                'start_date' => now(),
                'end_date' => now()->addDays(14),
                'status' => 'active',
            ]);
            
            for ($i = 1; $i <= $taskCount; $i++) {
                // Determine if this task should be assigned to sprint (70% chance)
                $sprintId = (rand(1, 10) <= 7) ? $sprint->id : null;
                
                // Randomly select fields
                $reporter = $members->random();
                $assignee = (rand(1, 10) <= 8) ? $members->random() : null; // 80% chance of being assigned
                $status = $statuses->random();
                $taskType = $taskTypes->random();
                $priority = $priorities->random();
                
                // Determine story points (null or 1-13 Fibonacci sequence)
                $storyPoints = (rand(1, 10) <= 7) ? $this->fibonacci(rand(1, 7)) : null;
                
                // Create the task
                $task = Task::create([
                    'title' => ucfirst(strtolower(str_replace('.', '', $taskDescriptions[array_rand($taskDescriptions)]))),
                    'description' => $this->generateDescription(),
                    'task_number' => $project->key . '-' . $i,
                    'project_id' => $project->id,
                    'reporter_id' => $reporter->id,
                    'assignee_id' => $assignee ? $assignee->id : null,
                    'task_status_id' => $status->id,
                    'task_type_id' => $taskType->id,
                    'priority_id' => $priority->id,
                    'sprint_id' => $sprintId,
                    'story_points' => $storyPoints,
                    'closed_at' => ($status->slug == 'done-' . $project->id) ? now()->subDays(rand(1, 5)) : null,
                    'created_at' => now()->subDays(rand(5, 30)),
                    'updated_at' => now()->subDays(rand(0, 4)),
                ]);
                
                // Add comments to some tasks (50% chance)
                if (rand(1, 2) == 1) {
                    $commentCount = rand(1, 5);
                    
                    for ($j = 1; $j <= $commentCount; $j++) {
                        DB::table('comments')->insert([
                            'content' => $this->generateComment(),
                            'task_id' => $task->id,
                            'user_id' => $members->random()->id,
                            'created_at' => now()->subDays(rand(0, 3))->subHours(rand(1, 23)),
                            'updated_at' => now()->subDays(rand(0, 3))->subHours(rand(1, 23)),
                        ]);
                    }
                }
                
                // Add time logs to some tasks (30% chance)
                if (rand(1, 10) <= 3 && $assignee) {
                    $timeLogCount = rand(1, 3);
                    
                    for ($j = 1; $j <= $timeLogCount; $j++) {
                        // Log between 15 minutes and 8 hours
                        $minutes = rand(1, 32) * 15;
                        
                        DB::table('time_logs')->insert([
                            'task_id' => $task->id,
                            'user_id' => $assignee->id,
                            'minutes' => $minutes,
                            'work_date' => now()->subDays(rand(0, 7)),
                            'description' => (rand(1, 2) == 1) ? $this->generateTimeLogDescription() : null,
                            'created_at' => now()->subDays(rand(0, 3)),
                            'updated_at' => now()->subDays(rand(0, 3)),
                        ]);
                    }
                }
            }
            
            // Create one completed sprint
            $completedSprint = Sprint::create([
                'name' => 'Sprint ' . date('Y-m', strtotime('-1 month')) . '-1',
                'project_id' => $project->id,
                'start_date' => now()->subDays(30),
                'end_date' => now()->subDays(16),
                'status' => 'completed',
            ]);
        }
    }
    
    /**
     * Generate a longer task description
     */
    private function generateDescription()
    {
        $descriptions = [
            "This task involves implementing the feature according to the specifications provided in the requirements document. Make sure to follow coding standards and write unit tests for the code. Consult with the design team if UI elements are involved.",
            
            "We need to refactor this component to improve performance and maintainability. The current implementation is causing memory leaks and slowdowns. Use best practices and update documentation after the changes.",
            
            "This bug has been reported by multiple users and is high priority. Steps to reproduce are included in the attached screenshots. The issue appears to be related to form validation and error handling.",
            
            "Create a new API endpoint following REST principles. The endpoint should support GET, POST, PUT, and DELETE operations. Ensure proper authentication and authorization checks are in place.",
            
            "Design and implement a database migration for the new feature. This will require adding new tables and modifying existing ones. Be careful with production data and provide a rollback plan.",
            
            "Improve the user experience on the dashboard by implementing the new design from Figma. Pay attention to responsive behavior and accessibility concerns.",
            
            "Investigate and fix the performance bottleneck in the reporting module. Users are experiencing timeouts when generating large reports. Consider implementing pagination or asynchronous processing.",
            
            "Integrate the third-party payment gateway using their official SDK. Implement proper error handling and transaction logging. This needs to be thoroughly tested in the staging environment.",
            
            "Update the documentation for the new features added in the last sprint. Include code examples and usage scenarios. The documentation should be accessible to both technical and non-technical users.",
            
            "Set up automated deployment pipeline using Jenkins or GitHub Actions. The pipeline should include linting, testing, building, and deploying to staging environments.",
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
    
    /**
     * Generate a comment for a task
     */
    private function generateComment()
    {
        $comments = [
            "I've started working on this task. Will update the status soon.",
            "Can someone clarify the requirements for this feature?",
            "I'm having trouble reproducing this bug. Can the reporter provide more details?",
            "I've completed the implementation. Please review my code in the PR.",
            "The changes look good to me. Approved for merging.",
            "There might be some edge cases we haven't considered here.",
            "This is taking longer than expected due to some unforeseen complications.",
            "I've pushed a fix to the development branch. Please test and confirm.",
            "Does this need to be backward compatible with the old API?",
            "We should consider performance implications of this approach.",
            "I suggest breaking this task down into smaller subtasks.",
            "This appears to be a duplicate of another task. Consider closing this one.",
            "The issue is fixed in the latest commit. Closing this task.",
            "I'm assigning this to myself and will work on it next week.",
            "Let's discuss this in the next standup meeting."
        ];
        
        return $comments[array_rand($comments)];
    }
    
    /**
     * Generate a description for time logs
     */
    private function generateTimeLogDescription()
    {
        $descriptions = [
            "Initial implementation of the feature",
            "Bug fixing and testing",
            "Code review and addressing feedback",
            "Refactoring for better performance",
            "Documentation and cleanup",
            "Meeting with stakeholders to clarify requirements",
            "Integration with other modules",
            "UI implementation and styling",
            "Setting up test environment",
            "Debugging production issue"
        ];
        
        return $descriptions[array_rand($descriptions)];
    }
    
    /**
     * Get Fibonacci sequence number
     */
    private function fibonacci($n)
    {
        $fibonacci = [1, 2, 3, 5, 8, 13, 21];
        return $fibonacci[min($n - 1, count($fibonacci) - 1)];
    }
}