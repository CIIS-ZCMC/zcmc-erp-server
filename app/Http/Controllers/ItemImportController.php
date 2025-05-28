<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\ItemCategory;
use App\Models\ItemClassification;
use App\Models\ItemReferenceTerminology;
use App\Models\Variant;
use App\Models\Snomed;
use App\Models\ItemSpecification;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;



class ItemImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new class implements \Maatwebsite\Excel\Concerns\ToCollection {
            public function collection(Collection $rows)
            {
                foreach ($rows as $index => $row) {
                    if ($index === 0) continue; // Skip header row

                    $classification = ItemClassification::firstWhere('name', trim($row[0]));
                    $category = ItemCategory::firstWhere('name', trim($row[1]));
                    $unit = ItemUnit::firstWhere('name', trim($row[3]));
                    $variant = ItemReferenceTerminology::where('system', 'Variant')
                        ->where('code', trim($row[8]))
                        ->first();

                    // Skip if any related reference is missing
                    if (!$classification || !$category || !$unit || !$variant) {
                        continue;
                    }

                    $item = Item::create([
                        'item_classification_id' => $classification->id,
                        'item_category_id' => $category->id,
                        'item_unit_id' => $unit->id,
                        'name' => trim($row[4]),
                        'code' => Str::slug($row[4]) . '-' . uniqid(),
                        'estimated_budget' => floatval($row[2]),
                    ]);

                    $specs = [trim($row[6]), trim($row[7])];
                    foreach ($specs as $spec) {
                        if ($spec) {
                            $item->itemSpecifications()->create([
                                'description' => $spec,
                            ]);
                        }
                    }
                }
            }
        }, $request->file('file'));

        return response()->json(['message' => 'Items imported successfully.']);
    }
}
