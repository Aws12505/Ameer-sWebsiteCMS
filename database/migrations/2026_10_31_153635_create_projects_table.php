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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('title', 40);
            $table->string('location', 40);
            $table->string('description', 150);
            $table->foreignId('main_category_id')->nullable()->constrained('project_categories')->nullOnDelete();
            $table->boolean('featured')->default(false);
            $table->string('cover_path')->nullable();
            $table->string('cover_alt', 125)->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamp('first_published_at')->nullable();
            $table->integer('sort_order')->default(0)->index();
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
        Schema::dropIfExists('projects');
    }
};
