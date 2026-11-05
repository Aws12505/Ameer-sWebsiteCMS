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
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 30);
            $table->string('email', 254);
            $table->string('project_type', 60)->nullable();
            $table->string('address', 120)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new')->index();
            $table->text('notes')->nullable();
            $table->string('fingerprint', 64)->index();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
