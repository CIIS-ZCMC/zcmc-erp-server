<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\ItemCategory;
use App\Models\ItemClassification;
use Symfony\Component\HttpFoundation\Response;

class ItemService
{
    // Method to create a single item
    public function store(array $data): Item
    {
        $is_valid_unit_id = ItemUnit::find($data['item_unit_id']);
        $is_valid_category_id = ItemCategory::find($data['item_category_id']);
        $is_valid_classification_id = ItemClassification::find($data['item_classification_id']);

        if (!($is_valid_unit_id && $is_valid_category_id && $is_valid_classification_id)) {
            return response()->json([
                "message" => "Invalid data given.",
                "metadata" => [
                    "methods" => ["GET, POST, PUT, DELETE"]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $cleanData = [
            "name" => strip_tags($data['name']),
            "code" => strip_tags($data['code']),
            "variant" => strip_tags($data['variant']),
            "estimated_budget" => strip_tags($data['estimated_budget']),
            "item_unit_id" => strip_tags($data['item_unit_id']),
            "item_category_id" => strip_tags($data['item_category_id']),
            "item_classification_id" => strip_tags($data['item_classification_id']),
        ];

        return Item::create($cleanData);
    }

}
