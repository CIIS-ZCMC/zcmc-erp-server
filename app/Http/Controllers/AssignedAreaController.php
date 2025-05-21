<?php

namespace App\Http\Controllers;

use App\Models\AssignedArea;
use App\Models\User;
use App\Services\UMISService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AssignedAreaController extends Controller
{
    /**
     * The UMIS service instance.
     *
     * @var \App\Services\UMISService
     */
    protected $umisService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\UMISService $umisService
     * @return void
     */
    public function __construct(UMISService $umisService)
    {
        $this->umisService = $umisService;
    }

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Get the latest data from UMIS
            $umisData = $this->umisService->getAreas();
            
            if (!$umisData) {
                Log::error('Failed to fetch areas from UMIS API');
                return response()->json([
                    'message' => 'Unable to retrieve area assignments'
                ], 500);
            }
            
            // Transform UMIS data to the format needed by the front-end
            $transformedData = collect($umisData)->map(function ($area) {
                // Add any transformation logic here if needed
                return $area;
            });
            
            return response()->json($transformedData);
        } catch (\Exception $e) {
            Log::error('Error in AssignedAreaController@index: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving area assignments'
            ], 500);
        }
    }

    /**
     * Process updates from UMIS.
     * This endpoint receives updates from UMIS when changes occur.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processUMISUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'area_data' => 'required|array',
                'area_data.*.user_id' => 'required|integer',
                'area_data.*.division_id' => 'nullable|integer',
                'area_data.*.department_id' => 'nullable|integer',
                'area_data.*.section_id' => 'nullable|integer',
                'area_data.*.unit_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updates = $request->input('area_data');
            $processed = 0;
            $skipped = 0;

            foreach ($updates as $update) {
                // Find user by UMIS ID
                $user = User::where('umis_id', $update['user_id'])->first();
                
                if (!$user) {
                    Log::warning("UMIS Update - User with UMIS ID {$update['user_id']} not found");
                    $skipped++;
                    continue;
                }
                
                // Update or create the assigned area
                AssignedArea::updateOrCreate(
        
                    [
                        'user_id' => $user->id,
                        'division_id' => $update['division_id'] ?? null,
                        'department_id' => $update['department_id'] ?? null,
                        'section_id' => $update['section_id'] ?? null,
                        'unit_id' => $update['unit_id'] ?? null,
                    ]
                );
                
                $processed++;
            }
            
            return response()->json([
                'message' => 'UMIS updates processed successfully',
                'processed' => $processed,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            Log::error('Error in AssignedAreaController@processUMISUpdate: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'An error occurred while processing UMIS updates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Get the area data from UMIS
            $areaData = $this->umisService->getArea($id);
            
            if (!$areaData) {
                return response()->json([
                    'message' => 'Area not found in UMIS'
                ], 404);
            }
            
            return response()->json($areaData);
        } catch (\Exception $e) {
            Log::error('Error in AssignedAreaController@show: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving area assignment'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        return response()->json([
            'message' => 'This operation is not supported. Areas are managed by UMIS.'
        ], 403);
    }
}
