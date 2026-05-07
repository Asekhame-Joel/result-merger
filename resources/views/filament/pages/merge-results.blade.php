<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Merge Test and Exam Scores
            </x-slot>

            <x-slot name="description">
                Select completed test and exam upload batches, then merge them by student ID or matric number.
            </x-slot>

            <div class="prose max-w-none dark:prose-invert">
                <p>
                    The merge engine will create final merged results with:
                </p>

                <ul>
                    <li><code>student_id</code></li>
                    <li><code>matric_no</code></li>
                    <li><code>first_name</code></li>
                    <li><code>last_name</code></li>
                    <li><code>Level</code></li>
                    <li><code>college</code></li>
                    <li><code>department</code></li>
                    <li><code>test_score</code></li>
                    <li><code>exam_score</code></li>
                    <li><code>total_score</code></li>
                </ul>
            </div>
        </x-filament::section>

        <div wire:poll.5s>
            @php
                $batch = $this->latestMergeBatch;
                $progress = $batch?->progressPercentage() ?? 0;
            @endphp

            <x-filament::section>
                <x-slot name="heading">
                    Latest Merge Progress
                </x-slot>

                @if ($batch)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium">
                                    {{ $batch->name ?? 'Unnamed Merge Batch' }}
                                </p>

                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Status: {{ $batch->status->label() }}
                                </p>
                            </div>

                            <div class="text-sm font-semibold">
                                {{ $progress }}%
                            </div>
                        </div>

                        <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-3 rounded-full bg-primary-600 transition-all duration-500"
                                style="width: {{ $progress }}%"></div>
                        </div>

                        <div class="grid gap-4 text-sm md:grid-cols-5">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Total</span>
                                <div class="font-semibold">{{ number_format($batch->total_rows) }}</div>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Processed</span>
                                <div class="font-semibold">{{ number_format($batch->processed_rows) }}</div>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Successful</span>
                                <div class="font-semibold text-success-600">{{ number_format($batch->successful_rows) }}
                                </div>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Failed</span>
                                <div class="font-semibold text-danger-600">{{ number_format($batch->failed_rows) }}</div>
                            </div>

                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Issues</span>
                                <div class="font-semibold text-warning-600">{{ number_format($batch->issue_count) }}</div>
                            </div>
                        </div>

                        @if ($batch->error_message)
                            <div
                                class="rounded-xl border border-danger-300 bg-danger-50 p-4 text-sm text-danger-700 dark:bg-danger-950">
                                {{ $batch->error_message }}
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No merge has been started yet.
                    </p>
                @endif
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                Processing Note
            </x-slot>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                During local development, keep
                <code>nice -n 15 php artisan queue:work --tries=1 --timeout=600 --memory=256 --sleep=1</code>
                running in a separate terminal.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>