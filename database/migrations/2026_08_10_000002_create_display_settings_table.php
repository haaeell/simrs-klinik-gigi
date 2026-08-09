<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('display_settings', function (Blueprint $table) {
            $table->id();
            $table->text('announcement_template');
            $table->string('chime_style')->default('ding-dong');
            $table->string('voice_name')->nullable();
            $table->string('voice_lang')->default('id-ID');
            $table->decimal('voice_rate', 3, 2)->default(0.95);
            $table->decimal('voice_pitch', 3, 2)->default(1.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('display_settings');
    }
};
