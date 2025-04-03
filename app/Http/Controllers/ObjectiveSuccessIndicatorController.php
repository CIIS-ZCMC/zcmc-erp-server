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
    
    public function index(Request $request)
    {
        $objective_success_indicator = ObjectiveSuccessIndicator::where('deleted_at', NULL)->get();

        return response()->json([
            "data" => ObjectiveSuccessIndicatorResource::collection($objective_success_indicator),
            "metadata" => [
                "methods" => ["GET, POST, PUT, DELETE"]
            ]
        ], Response::HTTP_OK);
    }

    /**
     * This function handles registration of 
     * {
     *    objective_id: 1, //optional
     *    objective_description: "value" //optional,
     *    objective_code: "value //optional,
     *    success_indicators: [
     *      {
     *          success_indicator_id: 1 // optional,
     *          success_indicator_description: "value" // optional,
     *          success_indicator_code: "value" //optional
     *      }  
     *    ]
     * }
     */
    public function store(Request $request):Response
    {
        $objective_id = $request->objective_id;
        $objective_code = $request->objective_code;
        $objective_description = $request->objective_description;
        $success_indicators_param = $request->success_indicators;

        if($objective_id !== null){
            $objective = Objective::find($objective_id);

            if(!$objective){
                return response()->json(["message" => "Objective not found"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $objective_success_indicators = [];

            foreach($success_indicators_param as $success_indicator)
            {
                // Both objective_id and success_indicator_id specified
                if(!$success_indicator['success_indicator_id']){
                    $success_indicator_data = SuccessIndicator::find($success_indicator['success_indicator_id']);

                    if(!$success_indicator_data){
                        return response()->json(["message" => "Success indicator not found"], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }

                    $new_objective_success_indicator = ObjectiveSuccessIndicator::create([
                        "objective_id" => $objective_id,
                        "success_indicator_id" => $success_indicator['success_indicator_id']
                    ]);

                    $objective_success_indicators[] = $new_objective_success_indicator;
                    continue;
                }

                if(!$success_indicator['success_indicator_code']){
                    return response()->json(["message" => "Success indicator id or code is required."], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $new_success_indicator = SuccessIndicator::create([
                    "code" => $success_indicator['code'],
                    "description" => $success_indicator['description'] !== null? strip_tags($success_indicator['description']): null
                ]);

                $new_objective_success_indicator = ObjectiveSuccessIndicator::create([
                    "objective_id" => $objective_id,
                    "success_indicator_id" => $new_success_indicator->id
                ]);

                $objective_success_indicators[] = $new_objective_success_indicator;
            }
        
            return response()->json([
                "data" => ObjectiveSuccessIndicatorResource::collection($objective_success_indicators),
                "metadata" => [
                    "methods" => ["GET, POST, DELETE"]
                ]
            ], Response::HTTP_CREATED);
        }

        if(!$objective_code){
            return response()->json(["message" => "Objective code is required."], Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $new_objective = Objective::create([
            "code" => $objective_code,
            "description" => $objective_description !== null? strip_tags($objective_description): null
        ]);

        $objective_success_indicators = [];

        foreach($success_indicators_param as $success_indicator)
        {
            // Both objective_id and success_indicator_id specified
            if(!$success_indicator['success_indicator_id']){
                $success_indicator_data = SuccessIndicator::find($success_indicator['success_indicator_id']);

                if(!$success_indicator_data){
                    return response()->json(["message" => "Success indicator not found"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $new_objective_success_indicator = ObjectiveSuccessIndicator::create([
                    "objective_id" => $new_objective->id,
                    "success_indicator_id" => $success_indicator['success_indicator_id']
                ]);

                $objective_success_indicators[] = $new_objective_success_indicator;
                continue;
            }

            if(!$success_indicator['success_indicator_code']){
                return response()->json(["message" => "Success indicator id or code is required."], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $new_success_indicator = SuccessIndicator::create([
                "code" => $success_indicator['code'],
                "description" => $success_indicator['description'] !== null? strip_tags($success_indicator['description']): null
            ]);

            $new_objective_success_indicator = ObjectiveSuccessIndicator::create([
                "objective_id" => $new_objective->id,
                "success_indicator_id" => $new_success_indicator->id
            ]);

            $objective_success_indicators[] = $new_objective_success_indicator;
        }
        
        return response()->json([
            "data" => ObjectiveSuccessIndicatorResource::collection($objective_success_indicators),
            "metadata" => [
                "methods" => ["GET, POST, DELETE"]
            ]
        ], Response::HTTP_CREATED);
    }

    public function destroy(Request $request, ObjectiveSuccessIndicator $objectiveSuccessIndicator)
    {
        $objectiveSuccessIndicator->update(['deleted_at' => now()]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
