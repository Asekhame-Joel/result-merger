<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Safe Cleanup Center
            </x-slot>

            <x-slot name="description">
                Use this page to reset or remove processing data safely.
            </x-slot>

            <div class="prose max-w-none dark:prose-invert">
                <p>
                    Cleanup actions are useful during testing, re-importing, or correcting uploaded files.
                    Be careful: most actions here cannot be undone.
                </p>
            </div>
        </x-filament::section>

        <div class="grid gap-6 md:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">
                    Test Uploads
                </x-slot>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Deletes all test score upload batches, test score records, related issues, and merged results linked
                    to those test batches.
                </p>

                <div class="mt-4">
                    <x-filament::button color="danger" icon="heroicon-o-trash" wire:click="deleteTestUploads"
                        wire:confirm="Delete all test uploads and related records?">
                        Delete All Test Uploads
                    </x-filament::button>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Exam Uploads
                </x-slot>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Deletes all exam score upload batches, exam score records, related issues, and merged results linked
                    to those exam batches.
                </p>

                <div class="mt-4">
                    <x-filament::button color="danger" icon="heroicon-o-trash" wire:click="deleteExamUploads"
                        wire:confirm="Delete all exam uploads and related records?">
                        Delete All Exam Uploads
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                Recommended Cleanup Order
            </x-slot>

            <div class="prose max-w-none dark:prose-invert">
                <ol>
                    <li>Delete merged results first if only the merge was wrong.</li>
                    <li>Delete test or exam uploads if the uploaded file was wrong.</li>
                    <li>Use reset all processing data only when you want to start completely fresh.</li>
                </ol>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>