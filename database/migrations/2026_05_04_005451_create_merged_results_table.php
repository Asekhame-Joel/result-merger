<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('merged_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('test_score_id')
                ->nullable()
                ->constrained('test_scores')
                ->nullOnDelete();

            $table->foreignId('exam_score_id')
                ->nullable()
                ->constrained('exam_scores')
                ->nullOnDelete();

            $table->foreignId('test_import_batch_id')
                ->nullable()
                ->constrained('import_batches')
                ->nullOnDelete();

            $table->foreignId('exam_import_batch_id')
                ->nullable()
                ->constrained('import_batches')
                ->nullOnDelete();

            $table->foreignId('merge_batch_id')
                ->nullable()
                ->constrained('import_batches')
                ->cascadeOnDelete();

            $table->string('student_id')->nullable();
            $table->string('matric_no')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('level')->nullable();
            $table->string('college')->nullable();
            $table->string('department')->nullable();

            $table->decimal('test_score', 8, 2)->nullable();
            $table->decimal('exam_score', 8, 2)->nullable();
            $table->decimal('total_score', 8, 2)->nullable();

            $table->string('grade')->nullable();
            $table->string('remark')->nullable();
            $table->decimal('grade_point', 4, 2)->nullable();

            $table->boolean('is_valid')->default(true);
            $table->text('validation_message')->nullable();

            $table->timestamps();

            $table->index('student_id');
            $table->index('matric_no');
            $table->index('level');
            $table->index('college');
            $table->index('department');
            $table->index('grade');
            $table->index('is_valid');

            $table->index('test_import_batch_id');
            $table->index('exam_import_batch_id');
            $table->index('merge_batch_id');

            $table->index(['merge_batch_id', 'student_id']);
            $table->index(['merge_batch_id', 'matric_no']);
            $table->index(['merge_batch_id', 'department']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merged_results');
    }
};