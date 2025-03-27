<?php

namespace App\Imports;

use App\Models\ItemClassification;
use App\Models\ItemCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ItemClassificationsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;
    
    private $rows = 0;
    private $categories;

    public function __construct()
    {
        $this->categories = ItemCategory::all()->keyBy('code');
    }

    public function model(array $row)
    {
        ++$this->rows;

        return new ItemClassification([
            'name'        => $row['name'],
            'code'        => $row['code'],
            'description' => $row['description'] ?? null
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:item_classifications,code',
            'description' => 'required|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'category_code.exists' => 'The category code :input does not exist.',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}