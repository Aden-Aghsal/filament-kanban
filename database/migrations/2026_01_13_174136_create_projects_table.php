<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  // ...
public function up(): void
{
    Schema::create('projects', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        
        // GANTI INI: Dulu client_name, sekarang priority
        $table->string('priority')->default('Normal'); 
        
        $table->string('status')->default('Planned');
        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();
        
        // Pastikan deskripsi tipe datanya TEXT atau LONGTEXT agar muat banyak kode HTML/Gambar
        $table->longText('description')->nullable();
        
        $table->foreignId('leader_id')->constrained('users')->onDelete('cascade');
        
        $table->string('visibility')->default('private'); 
        $table->boolean('is_archived')->default(false);
        $table->timestamps();
    });
}
// ...

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};