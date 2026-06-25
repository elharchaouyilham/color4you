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
        Schema::create('drawing_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_profile_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('registered_count')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('status')->default('draft')->index();
            $table->text('trainer_response_note')->nullable();
            $table->timestamp('trainer_responded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trainer_profile_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drawing_sessions');
    }
};
