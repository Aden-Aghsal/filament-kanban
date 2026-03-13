<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['project_id', 'sort_order'], 'tasks_project_sort_index');
            $table->index(['user_id', 'due_date'], 'tasks_user_due_index');
            $table->index(['project_id', 'status'], 'tasks_project_status_index');
        });

        Schema::table('project_user', function (Blueprint $table) {
            $table->index(['project_id', 'user_id'], 'project_user_project_user_index');
            $table->index(['user_id', 'project_id'], 'project_user_user_project_index');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index(['is_archived', 'status'], 'projects_archived_status_index');
            $table->index('priority', 'projects_priority_index');
            $table->index('leader_id', 'projects_leader_index');
            $table->index('start_date', 'projects_start_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_archived_status_index');
            $table->dropIndex('projects_priority_index');
            $table->dropIndex('projects_leader_index');
            $table->dropIndex('projects_start_date_index');
        });

        Schema::table('project_user', function (Blueprint $table) {
            $table->dropIndex('project_user_project_user_index');
            $table->dropIndex('project_user_user_project_index');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_project_sort_index');
            $table->dropIndex('tasks_user_due_index');
            $table->dropIndex('tasks_project_status_index');
        });
    }
};
