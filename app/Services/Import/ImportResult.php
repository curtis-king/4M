<?php

namespace App\Services\Import;

class ImportResult
{
    public int $imported = 0;
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;
    /** @var array<int, array{line:int, message:string, row:array}> */
    public array $errors = [];
    /** @var array<int, array{line:int, message:string}> */
    public array $warnings = [];

    public function addError(int $line, string $message, array $row = []): void
    {
        $this->errors[] = ['line' => $line, 'message' => $message, 'row' => $row];
    }

    public function addWarning(int $line, string $message): void
    {
        $this->warnings[] = ['line' => $line, 'message' => $message];
    }

    public function isSuccessful(): bool
    {
        return empty($this->errors);
    }

    public function summary(): array
    {
        return [
            'imported' => $this->imported,
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => count($this->errors),
            'warnings' => count($this->warnings),
        ];
    }
}
