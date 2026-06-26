<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seance_dessins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('prix', 8, 2);
            $table->dateTime('date_heure');
            $table->integer('max_participants');
            $table->enum('status', ['En attente du formateur', 'Ouverte', 'Complète', 'Annulée'])->default('En attente du formateur');
            $table->foreignId('formateur_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seance_dessins');
    }
};