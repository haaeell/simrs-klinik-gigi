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
        Schema::create('odontogram_teeth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontogram_id')->constrained()->cascadeOnDelete();
            $table->string('tooth_number');
            $table->string('condition')->nullable();
            $table->string('surfaces')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['odontogram_id', 'tooth_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontogram_teeth');
    }
};
