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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name', 40);
            $table->string('project', 60);
            $table->unsignedTinyInteger('rating');
            $table->string('quote', 200);
            $table->string('photo_path')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamp('first_published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
