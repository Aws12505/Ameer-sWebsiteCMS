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
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('title', 40);
            $table->string('filter_label', 20);
            $table->string('summary', 160);
            $table->string('image_path')->nullable();
            $table->string('image_alt', 125)->nullable();
            $table->json('body')->nullable();
            $table->json('gallery')->nullable();
            $table->string('external_url', 200)->nullable();
            $table->boolean('show_on_home')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->string('seo_title', 60)->nullable();
            $table->string('seo_description', 160)->nullable();
            $table->string('seo_image_path')->nullable();
            $table->string('seo_image_alt', 125)->nullable();
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
        Schema::dropIfExists('service_categories');
    }
};
