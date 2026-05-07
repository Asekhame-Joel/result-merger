<?php

use App\Enums\ResultIssueSeverity;
use App\Enums\ResultIssueStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('result_issues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('import_batch_id')
                ->nullable()
                ->constrained('import_batches')
                ->cascadeOnDelete();

            $table->foreignId('merged_result_id')
                ->nullable()
                ->constrained('merged_results')
                ->cascadeOnDelete();

            $table->foreignId('test_score_id')
                ->nullable()
                ->constrained('test_scores')
                ->cascadeOnDelete();

            $table->foreignId('exam_score_id')
                ->nullable()
                ->constrained('exam_scores')
                ->cascadeOnDelete();

            $table->string('type');
            $table->string('severity')->default(ResultIssueSeverity::Error->value);
            $table->string('status')->default(ResultIssueStatus::Open->value);

            $table->text('message');

            $table->unsignedInteger('row_number')->nullable();

            $table->string('student_id')->nullable();
            $table->string('matric_no')->nullable();
            $table->string('level')->nullable();
            $table->string('department')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('type');
            $table->index('severity');
            $table->index('status');
            $table->index('student_id');
            $table->index('matric_no');
            $table->index('level');
            $table->index('department');
            $table->index('import_batch_id');
            $table->index('merged_result_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_issues');
    }
};