<?php

namespace App\Http\Controllers;

use App\Models\ItemUnit;
use App\Models\LogDescription;
use App\Models\TransactionLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemUnitController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->query('page');
        $per_page = $request->query('per_page');
        
        $total_page = ItemUnit::all()->pluck('id')->chunk($per_page);

        return response()->json([
            "data" => $total_page,
            "metadata" => [
                "methods" => "[GET, POST, PUT, DELETE]",
                "pagination" => [],
                "page" => $page,
                "total_page" => $total_page
            ]
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $new_item = ItemUnit::create([]);

        $log = new TransactionLog();
        $log->log_description_id = LogDescription::where('code', "IU-POST")->first()->id;
        $log->referrence()->associate($new_item);
        $log->save();
    }
}
