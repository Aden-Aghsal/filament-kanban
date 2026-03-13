<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            
            // --- INI YANG HILANG BRO! TAMBAHKAN BARIS INI ---
            // Pakai nullable() karena task baru mungkin belum ada assignee-nya
            // Pakai nullOnDelete() supaya kalau usernya dihapus, tasknya nggak ikut kehapus (cuma jadi unassigned)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // ------------------------------------------------

            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('status')->default('Initiated')->index(); 
            
            $table->json('subtasks')->nullable();
            $table->string('priority')->default('Normal'); 
            $table->json('comments')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};