<?php

namespace App\Services\Imports;

use App\Enums\ImportBatchStatus;
use App\Enums\ImportBatchType;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImportUploadGuard
{
    public function validateCsvUpload(string $disk, string $filePath): string
    {
        if (!Storage::disk($disk)->exists($filePath)) {
            throw new RuntimeException('Uploaded file could not be found.');
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($extension, ['csv', 'txt'], true)) {
            throw new RuntimeException('Only CSV files are allowed. Save your Excel file as CSV UTF-8 before uploading.');
        }

        $fullPath = Storage::disk($disk)->path($filePath);

        $hash = hash_file('sha256', $fullPath);

        if (!$hash) {
            throw new RuntimeException('Could not generate uploaded file hash.');
        }

        return $hash;
    }

    public function preventActiveDuplicate(ImportBatchType $type, string $fileHash): void
    {
        $exists = ImportBatch::query()
            ->where('type', $type)
            ->where('file_hash', $fileHash)
            ->whereIn('status', [
                ImportBatchStatus::Pending,
                ImportBatchStatus::Processing,
            ])
            ->exists();

        if ($exists) {
            throw new RuntimeException('This file is already pending or processing. Please wait for the current import to finish.');
        }
    }

    public function preventAnyDuplicate(ImportBatchType $type, string $fileHash): void
    {
        $exists = ImportBatch::query()
            ->where('type', $type)
            ->where('file_hash', $fileHash)
            ->whereIn('status', [
                ImportBatchStatus::Completed,
                ImportBatchStatus::CompletedWithIssues,
            ])
            ->exists();

        if ($exists) {
            throw new RuntimeException('This file appears to have already been imported. Delete the old batch first if you want to re-import it.');
        }
    }
}