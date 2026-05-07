<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Export Merged Results
            </x-slot>

            <x-slot name="description">
                Export final merged results as a CSV file.
            </x-slot>

            <div class="prose max-w-none dark:prose-invert">
                <p>
                    The exported file will contain:
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
        @if ($latestExportPath)
            <x-filament::section>
                <x-slot name="heading">
                    Latest Export
                </x-slot>

                <div class="space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        File generated successfully:
                        <code>{{ basename($latestExportPath) }}</code>
                    </p>

                    <x-filament::button wire:click="downloadLatestExport" icon="heroicon-o-arrow-down-tray">
                        Download File
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">
                Recommendation
            </x-slot>

            <p class="text-sm text-gray-600 dark:text-gray-300">
                CSV export is used first because it is lighter and faster. After the full result workflow is stable,
                we can add XLSX export as an optional format.
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>