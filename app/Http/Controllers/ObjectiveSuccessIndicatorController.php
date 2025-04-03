<?php

namespace App\Http\Controllers;

use App\Http\Resources\ObjectiveSuccessIndicatorResource;
use App\Models\Objective;
use App\Models\ObjectiveSuccessIndicator;
use App\Models\SuccessIndicator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ObjectiveSuccessIndicatorController extends Controller
{
    
    /**
     * This function handles registration of 
     * {
     *    objective_description: "value",
     *    objective_code: "value,
     *    success_indicato_description: "value",
     *    success_indicator_code: "value"
     * }
     */
    public function store(Request $request)
    {

        if($request->objective_code === null || $request->success_indicator_code === null){
            return response()->json(["message"=> ""], Response::HTTP_BAD_REQUEST);
        }

        $objective = [
            "code" => strip_tags($request->objective_code),
            "description" => strip_tags($request->objective_description)
        ];

        $success_indicator = [
            "code" => strip_tags($request->success_indicator_code),
            "description" => strip_tags($request->success_indicator_description)
        ];

        $new_objective = Objective::create($objective);
        $new_success_indicator = SuccessIndicator::create($success_indicator);

        $object_success_indicator = ObjectiveSuccessIndicator::create([
            "objective_id" => $new_objective->id,
            "success_indicator_id" => $new_success_indicator->id
        ]);
        
        return response()->json([
            "data" => new ObjectiveSuccessIndicatorResource($object_success_indicator),
            "metadata" => [
                "methods" => ["GET, POST, PUT, DELETE"]
            ]
        ], Response::HTTP_OK);
    }
}
