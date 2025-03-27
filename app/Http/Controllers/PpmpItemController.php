<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PpmpItemController extends Controller
{
    public function store(Request $request): Response
    {
        /**
         * REGISTRATION OF NEW ITEM ON USER PPMP
         * 
         * This task will handle registration of ppmp items 
         * it must support registration of existing and non existing item.
         * 
         * Check first if new item has value
         * 
         * request body
         * {
         *  ppmp_application_id: 1,
         *  item_id: 1,
         *  modes_of_procurement_id,
         *  remarks: "New Item",
         *  total_amount: 250.00,
         *  new_item: { // Nullable
         *      name: "Desktop",
         *      estimated_budget: 120,000.00,
         *      unit_id: 1,
         *      category_id: 1,
         *      classification_id: 1,
         *      ppmp_application_id: 1,
         *      modes_of_procurement_id: 1,
         *      remarks: "New Item",
         *      total_amount: 120,000.00
         *  }
         * }
         * 
         * priority new_item if has value register first item_request then create ppmp
         */

         $new_item = $request->new_item;

         if($new_item){
            /**
             * 1st Regsiter new item to item request
             * 2nd Register register ppmp items associate the new item request
             * 
             * return the ppmp items structure must be the same with the registered item.
             */

            return response()->json([
                "data" => null,
                "message" => "Successfully created item request and ppmp item",
                "metadata" => [
                    "methods" => ["GET, POST, PUT, DELETE"]
                ]
            ], Response::HTTP_CREATED);
         }

         /**
          * Register ppmp item
          * Return ppmp items
          */

          return response()->json([
            "data" => null,
            "message" => "Successfully created ppmp item.",
            "metadata" => [
                "methods" => ["GET, POST, PUT, DELETE"]
            ]
          ], Response::HTTP_CREATED);
    }
}
