<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('friendships', function (Blueprint $table) {
        $table->id();
        // Pengirim Request
        $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
        // Penerima Request
        $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
        // Status: pending (menunggu), accepted (berteman), rejected (ditolak)
        $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
        $table->timestamps();

        // Mencegah duplikat (A gak bisa add B dua kali)
        $table->unique(['sender_id', 'recipient_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
