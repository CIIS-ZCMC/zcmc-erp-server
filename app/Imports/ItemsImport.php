<?php

namespace App\Imports;

use App\Models\CategoryConsolidator;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemReferenceTerminology;
use App\Models\ItemSpecification;
use App\Models\ItemUnit;
use App\Models\TerminologyCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;

class ItemsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    private Collection $categories;
    private Collection $units;
    private Collection $referrence_terminologies;
    private int $processed = 0;
    private int $skipped = 0;

    public function __construct()
    {
        $this->preloadData();
    }

    private function preloadData(): void
    {
        $this->categories = ItemCategory::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id]);

        $this->units = ItemUnit::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id]);

        $this->referrence_terminologies = ItemReferenceTerminology::pluck('id', 'code')
            ->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id]);
    }
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        /**
         * Excel header
         * category
         * estimated_budget
         * unit
         * name
         * specs1
         * specs2
         * terminology
         */

        $categoryId = $this->categories[strtolower($row['category'])] ?? null;
        $unitId = $this->units[strtolower($row['unit'])] ?? null;
        $referrenceTerminologyId = $this->referrence_terminologies[strtolower($row['terminology'])] ?? null;

        if (!$categoryId || !$unitId || !$referrenceTerminologyId) {
            $this->skipped++;
            \Log::warning("Skipped row {$this->processed}: Invalid data");
            return null;
        }
   
        // Search terminology
        $terminology = TerminologyCategory::where('category_id', $categoryId)->where('referrence_terminology_id', $referrenceTerminologyId)->first();        
        
        $this->processed++;
        return DB::transaction(function() use ($row, $categoryId, $unitId, $terminology){

            // Convert formatted string to float (e.g., "88,288.00" → 88288.00)
            $estimatedBudget = $this->parseNumericValue($row['estimated_budget']);

            // Create Item
            $newItem = Item::create([
                'name'        => $row['name'],
                'estimated_budget' => $estimatedBudget,
                'category_id' => $categoryId,
                'unit' => $unitId,
                'terminology_id' => $terminology->id
            ]);

            if(!empty($row['specs1'])){
                // Register Item specification
                ItemSpecification::create([
                    'description' => $row['specs1'],
                    'item_id' => $newItem->id
                ]);
            }

            if(!empty($row['specs2'])){
                // Register Item specification
                ItemSpecification::create([
                    'description' => $row['specs2'],
                    'item_id' => $newItem->id
                ]);
            }

            return $newItem;
        });
    }

    public function rules(): array
    {
        return [
            'category' => 'required|string|max:255',
            'estimated_budget' => 'required|string|max:50',
            'unit' => 'required|string|min:0',
            'name' => 'nullable|string|min:0',
            'specs1' => 'nullable|string',
            'specs2' => 'nullable|string',
            'terminology' => 'nullable|string',
        ];
    }

    private function parseNumericValue($value)
    {
        if (is_null($value)) {
            return 0;
        }

        // Remove thousands separators (commas) and convert to float
        return floatval(str_replace(',', '', $value));
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
    
    public function getResult(): array
    {
        return [
            'processed' => $this->processed,
            'skipped' => $this->skipped,
            'total' => $this->processed + $this->skipped
        ];
    }
}
