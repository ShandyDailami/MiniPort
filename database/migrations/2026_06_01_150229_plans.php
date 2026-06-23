<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained();
            $table->string('plan_name');
            $table->text('description')->nullable(); // Tambahan untuk deskripsi
            $table->decimal('price', 8, 2);
            $table->integer('storage_limit_mb');
            $table->integer('max_buckets')->nullable(); // Tambahan untuk batas bucket
            $table->integer('max_file_size_mb')->nullable(); // Tambahan untuk batas ukuran file
            $table->boolean('allow_presigned_links')->default(true); // Tambahan untuk izin share link
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};