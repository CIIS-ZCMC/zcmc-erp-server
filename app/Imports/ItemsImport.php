<?php

namespace App\Imports;

use App\Models\CategoryConsolidator;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemClassification;
use App\Models\ItemReferenceTerminology;
use App\Models\ItemSpecification;
use App\Models\ItemUnit;
use App\Models\TerminologyCategory;
use Illuminate\Support\Collection;
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
    private Collection $refference;
    private int $processed = 0;
    private int $skipped = 0;

    public function __construct()
    {
        $this->preloadData();
    }

    private function preloadData(): void
    {
        \Illuminate\Support\Facades\Log::warning("Import class instantiated"); // Add this
        $this->categories = ItemCategory::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);

        $this->units = ItemUnit::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id]);

        $this->refference = ItemReferenceTerminology::pluck('id', 'code')
            ->mapWithKeys(fn($id, int|string $name) => [strtolower($name) => $id]);
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
        $referrenceId = $this->refference[strtolower($row['terminology'])] ?? null;

        if (!$categoryId) {
            \Log::warning("Failed here category: {$row['category']}");
        }

        if (!$unitId) {
            \Log::warning("Failed here unit: {$row['unit']}");
        }

        if (!$referrenceId) {
            \Log::warning("Failed here referrence: {$row['terminology']}");
        }

        if (!$categoryId || !$unitId || !$referrenceId) {
            $this->skipped++;
            \Log::warning("Skipped row processed:{$this->processed} category: {$categoryId}  terminology: {$row['terminology']} : Invalid data");
            return null;
        }



        $terminology = TerminologyCategory::where('category_id', $categoryId)->where('reference_terminology_id', $referrenceId)->first();
        $terminologyId = $terminology->id;

        $this->processed++;
        return DB::transaction(function () use ($row, $categoryId, $unitId, $terminologyId) {

            // Convert formatted string to float (e.g., "88,288.00" → 88288.00)
            $estimatedBudget = $this->parseNumericValue($row['estimated_budget']);

            // Create Item
            $newItem = Item::create([
                'name'        => $row['name'],
                'estimated_budget' => $estimatedBudget,
                'item_category_id' => $categoryId,
                'item_unit_id' => $unitId,
                'terminologies_category_id' => $terminologyId
            ]);

            if (!empty($row['specs1'])) {
                // Register Item specification
                ItemSpecification::create([
                    'description' => $row['specs1'],
                    'item_id' => $newItem->id
                ]);
            }

            if (!empty($row['specs2'])) {
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
            // 'category' => 'required|string|max:255',
            // 'estimated_budget' => 'required|string|max:50',
            // 'unit' => 'required|string|min:0',
            // 'name' => 'nullable|string|min:0',
            // 'specs1' => 'nullable|string',
            // 'specs2' => 'nullable|string',
            // 'terminology' => 'nullable|string',
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
