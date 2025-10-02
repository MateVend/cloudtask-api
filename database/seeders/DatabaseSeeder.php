<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo user
        $user = User::create([
            'name' => 'Daggy User',
            'email' => 'daggy@example.com',
            'password' => Hash::make('Tildaroyce*'),
        ]);

        // Create demo organization
        $organization = Organization::create([
            'name' => 'Demo Organization',
            'plan' => 'pro',
            'project_limit' => 50,
            'user_limit' => 20,
        ]);

        $organization->users()->attach($user->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        // Create additional users
        $user2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'current_organization_id' => $organization->id,
        ]);
        $organization->users()->attach($user2->id, ['role' => 'member']);

        $user3 = User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'current_organization_id' => $organization->id,
        ]);
        $organization->users()->attach($user3->id, ['role' => 'manager']);

        // Create demo projects
        $project1 = Project::create([
            'organization_id' => $organization->id,
            'name' => 'Website Redesign',
            'description' => 'Complete overhaul of company website',
            'status' => 'active',
            'color' => '#3b82f6',
            'created_by' => $user->id,
        ]);

        $project2 = Project::create([
            'organization_id' => $organization->id,
            'name' => 'Mobile App Development',
            'description' => 'Build iOS and Android applications',
            'status' => 'active',
            'color' => '#10b981',
            'created_by' => $user->id,
        ]);

        $project3 = Project::create([
            'organization_id' => $organization->id,
            'name' => 'Marketing Campaign',
            'description' => 'Q4 2024 marketing initiatives',
            'status' => 'on_hold',
            'color' => '#f59e0b',
            'created_by' => $user->id,
        ]);

        // Create demo tasks for project 1
        Task::create([
            'project_id' => $project1->id,
            'organization_id' => $organization->id,
            'title' => 'Design new homepage',
            'description' => 'Create mockups for the new homepage design',
            'status' => 'completed',
            'priority' => 'high',
            'assigned_to' => $user2->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(7),
            'estimated_hours' => 16,
        ]);

        Task::create([
            'project_id' => $project1->id,
            'organization_id' => $organization->id,
            'title' => 'Develop frontend components',
            'description' => 'Build React components for new design',
            'status' => 'in_progress',
            'priority' => 'high',
            'assigned_to' => $user3->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(14),
            'estimated_hours' => 40,
        ]);

        Task::create([
            'project_id' => $project1->id,
            'organization_id' => $organization->id,
            'title' => 'Setup CI/CD pipeline',
            'description' => 'Configure automated deployment',
            'status' => 'todo',
            'priority' => 'medium',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(21),
            'estimated_hours' => 8,
        ]);

        // Create demo tasks for project 2
        Task::create([
            'project_id' => $project2->id,
            'organization_id' => $organization->id,
            'title' => 'Design app wireframes',
            'description' => 'Create wireframes for all app screens',
            'status' => 'completed',
            'priority' => 'urgent',
            'assigned_to' => $user2->id,
            'created_by' => $user->id,
            'due_date' => now()->subDays(3),
            'estimated_hours' => 20,
        ]);

        Task::create([
            'project_id' => $project2->id,
            'organization_id' => $organization->id,
            'title' => 'Implement user authentication',
            'description' => 'Build login and registration flows',
            'status' => 'in_progress',
            'priority' => 'high',
            'assigned_to' => $user3->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(10),
            'estimated_hours' => 24,
        ]);

        Task::create([
            'project_id' => $project2->id,
            'organization_id' => $organization->id,
            'title' => 'Test on multiple devices',
            'description' => 'QA testing across different screen sizes',
            'status' => 'review',
            'priority' => 'medium',
            'assigned_to' => $user2->id,
            'created_by' => $user->id,
            'due_date' => now()->addDays(5),
            'estimated_hours' => 12,
        ]);
    }
}
