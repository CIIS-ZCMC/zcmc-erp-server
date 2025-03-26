<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcurementModeRequest;
use App\Http\Resources\ProcurementModeResource;
use App\Models\ProcurementModes;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProcurementModesController extends Controller
{
    private $module = 'procurement-modes';

    private function getMetaData()
    {
        return ["methods" => ['GET, POST, PUT, DELETE']];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $procurementModes = ProcurementModes::where('deleted_at', NULL)->all();

        return response()->json([
            "data" => ProcurementModeResource::collection($procurementModes),
            "metadata" => $this->getMetaData(),
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcurementModeRequest $request)
    {
        $name = $request->input("name");

        if(!$name){
            return response()->json([
                "message" => "Field name is required."
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cleanData = ['name' => strip_tags($name)];

        $has_record = ProcurementModes::where('name', $name)->get();

        if(count($has_record) > 0){
            return response()->json(['message' => "Procurement mode already exist."], Response::HTTP_BAD_REQUEST);
        }

        $procurement_mode = ProcurementModes::create($cleanData);

        return response()->json([
            "data" => $procurement_mode,
            "metadata" => $this->getMetaData()
        ], Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProcurementModes $procurementMode)
    {
        $name = strip_tags($request->input("name"));

        $procurementMode->update($request->all());

        return response()->json([
            'data' => $procurementMode,
            "metadata" => $this->getMetaData()
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProcurementModes $procurementMode)
    {
        $procurementMode->update(['deleted_at' => now()]);

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
