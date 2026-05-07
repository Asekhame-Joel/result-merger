<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_scores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('import_batch_id')
                ->constrained('import_batches')
                ->cascadeOnDelete();

            $table->string('student_id')->nullable();
            $table->string('matric_no')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('level')->nullable();
            $table->string('college')->nullable();
            $table->string('department')->nullable();

            $table->decimal('exam_score', 8, 2)->nullable();

            $table->unsignedInteger('row_number')->nullable();

            $table->boolean('is_valid')->default(true);
            $table->text('validation_message')->nullable();

            $table->timestamps();

            $table->index('student_id');
            $table->index('matric_no');
            $table->index('level');
            $table->index('college');
            $table->index('department');
            $table->index('import_batch_id');
            $table->index(['import_batch_id', 'student_id']);
            $table->index(['import_batch_id', 'matric_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_scores');
    }
};