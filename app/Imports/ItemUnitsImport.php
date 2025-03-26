<?php

namespace App\Imports;

use App\Models\ItemUnit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ItemUnitsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;
    
    private $rows = 0;
    private $errors = [];

    public function model(array $row)
    {
        ++$this->rows;
        
        return new ItemUnit([
            'name'        => $row['name'],
            'code'        => $row['code'],
            'description' => $row['description'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:item_units,code',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'The name field is required.',
            'code.required' => 'The code field is required.',
            'code.unique' => 'The code already exists.',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}