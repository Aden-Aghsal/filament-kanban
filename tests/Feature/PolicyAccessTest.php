<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PolicyAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'member']);
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_task_policy_access_matrix(): void
    {
        $admin = $this->makeUser('admin');
        $leader = $this->makeUser('member');
        $assignee = $this->makeUser('member');
        $member = $this->makeUser('member');
        $outsider = $this->makeUser('member');

        $project = Project::create([
            'name' => 'Project A',
            'status' => 'Planned',
            'priority' => 'Normal',
            'leader_id' => $leader->id,
        ]);

        $project->members()->attach([$leader->id, $assignee->id, $member->id]);

        $task = Task::create([
            'project_id' => $project->id,
            'user_id' => $assignee->id,
            'title' => 'Task A',
            'status' => TaskStatus::Initiated->value,
            'priority' => 'Normal',
        ]);

        $this->assertTrue($admin->can('view', $task));
        $this->assertTrue($leader->can('view', $task));
        $this->assertTrue($assignee->can('view', $task));
        $this->assertTrue($member->can('view', $task));
        $this->assertFalse($outsider->can('view', $task));

        $this->assertTrue($admin->can('update', $task));
        $this->assertTrue($leader->can('update', $task));
        $this->assertTrue($assignee->can('update', $task));
        $this->assertTrue($member->can('update', $task));
        $this->assertFalse($outsider->can('update', $task));

        $this->assertTrue($admin->can('delete', $task));
        $this->assertTrue($leader->can('delete', $task));
        $this->assertTrue($assignee->can('delete', $task));
        $this->assertFalse($member->can('delete', $task));
        $this->assertFalse($outsider->can('delete', $task));
    }

    public function test_project_policy_access_matrix(): void
    {
        $admin = $this->makeUser('admin');
        $leader = $this->makeUser('member');
        $member = $this->makeUser('member');
        $outsider = $this->makeUser('member');

        $project = Project::create([
            'name' => 'Project B',
            'status' => 'Planned',
            'priority' => 'Normal',
            'leader_id' => $leader->id,
        ]);

        $project->members()->attach([$leader->id, $member->id]);

        $this->assertTrue($admin->can('view', $project));
        $this->assertTrue($leader->can('view', $project));
        $this->assertTrue($member->can('view', $project));
        $this->assertFalse($outsider->can('view', $project));

        $this->assertTrue($admin->can('update', $project));
        $this->assertTrue($leader->can('update', $project));
        $this->assertFalse($member->can('update', $project));
        $this->assertFalse($outsider->can('update', $project));

        $this->assertTrue($admin->can('delete', $project));
        $this->assertTrue($leader->can('delete', $project));
        $this->assertFalse($member->can('delete', $project));
        $this->assertFalse($outsider->can('delete', $project));
    }

    public function test_user_policy_access_matrix(): void
    {
        $admin = $this->makeUser('admin');
        $member = $this->makeUser('member');
        $other = User::factory()->create();

        $this->assertTrue($admin->can('viewAny', User::class));
        $this->assertTrue($admin->can('create', User::class));
        $this->assertTrue($admin->can('update', $other));
        $this->assertTrue($admin->can('delete', $other));

        $this->assertFalse($member->can('viewAny', User::class));
        $this->assertFalse($member->can('create', User::class));
        $this->assertFalse($member->can('update', $other));
        $this->assertFalse($member->can('delete', $other));
    }
}
