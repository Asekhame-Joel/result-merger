<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grading_settings', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->decimal('test_max', 8, 2)->default(40);
            $table->decimal('exam_max', 8, 2)->default(60);
            $table->decimal('total_max', 8, 2)->default(100);

            $table->boolean('is_active')->default(false);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_settings');
    }
};