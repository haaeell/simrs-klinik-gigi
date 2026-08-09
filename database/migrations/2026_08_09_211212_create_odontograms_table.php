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
        Schema::create('odontograms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('visit_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->date('examination_date');
            $table->string('occlusion')->nullable();
            $table->string('torus_palatinus')->nullable();
            $table->string('torus_mandibularis')->nullable();
            $table->string('palate')->nullable();
            $table->string('diastema')->nullable();
            $table->string('dental_anomaly')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odontograms');
    }
};
