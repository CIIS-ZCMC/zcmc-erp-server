<?php

namespace App\Imports;

use App\Models\LogDescription;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class LogDescriptionsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;
    
    private $rows = 0;

    public function model(array $row)
    {
        ++$this->rows;
        
        return new LogDescription([
            'title' => $row['title'],
            'code' => $row['code'],
            'description' => $row['description'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:log_descriptions,code',
            'description' => 'nullable|string',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}