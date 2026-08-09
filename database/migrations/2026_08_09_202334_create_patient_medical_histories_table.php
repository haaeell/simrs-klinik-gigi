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
        Schema::create('patient_medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('blood_type')->nullable();
            $table->unsignedSmallInteger('systolic')->nullable();
            $table->unsignedSmallInteger('diastolic')->nullable();
            $table->boolean('heart_disease')->default(false);
            $table->boolean('diabetes')->default(false);
            $table->boolean('haemophilia')->default(false);
            $table->boolean('hepatitis')->default(false);
            $table->string('other_disease')->nullable();
            $table->string('drug_allergy')->nullable();
            $table->string('food_allergy')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medical_histories');
    }
};
