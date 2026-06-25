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
        Schema::create('session_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('drawing_session_id')->constrained()->restrictOnDelete();
            $table->timestamp('registered_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('status')->default('registered')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'drawing_session_id']);
            $table->index(['drawing_session_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_registrations');
    }
};
