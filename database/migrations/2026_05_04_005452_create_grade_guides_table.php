<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_guides', function (Blueprint $table) {
            $table->id();

            $table->decimal('minimum_score', 8, 2);
            $table->decimal('maximum_score', 8, 2);

            $table->string('grade');
            $table->string('remark')->nullable();
            $table->decimal('grade_point', 4, 2)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('grade');
            $table->index('is_active');
            $table->index(['minimum_score', 'maximum_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_guides');
    }
};