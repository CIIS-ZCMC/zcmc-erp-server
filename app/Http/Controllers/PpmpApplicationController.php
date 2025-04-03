<?php

namespace App\Http\Controllers;

use App\Http\Requests\PpmpApplicationRequest;
use App\Http\Resources\PpmpApplicationResource;
use App\Models\PpmpApplication;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PpmpApplicationController extends Controller
{
    private $is_development;

    private $module = 'ppmp_applicationss';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }


    protected function getMetadata($method): array
    {
        if ($method === 'get') {
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?ppmp_application_id=[primary-key]",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ];
            }

            return $metadata;
        }

        if ($method === 'put') {
            $metadata = ["methods" => "[PUT]"];

            if ($this->is_development) {
                $metadata["urls"] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                ];
                $metadata['fields'] = ["type"];
            }

            return $metadata;
        }

        $metadata = ['methods' => ["GET, PUT, DELETE"]];

        if ($this->is_development) {
            $metadata["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
            ];

            $metadata["fields"] = ["type"];
        }

        return $metadata;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //paginate display 10 data per page
        $ppmp_application = PpmpApplication::whereNull('deleted_at')->paginate(10);

        if ($ppmp_application->isEmpty()) {
            return response()->json([
                'message' => "No record found.",
                "metadata" => $this->getMetadata('get')
            ], Response::HTTP_OK);
        }

        return response()->json([
            'data' => PpmpApplicationResource::collection($ppmp_application),
            'pagination' => [
                'current_page' => $ppmp_application->currentPage(),
                'last_page' => $ppmp_application->lastPage(),
                'per_page' => $ppmp_application->perPage(),
                'total' => $ppmp_application->total(),
            ],
            'message' => $this->getMetadata('get'),
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PpmpApplicationRequest $request)
    {
        $data = new PpmpApplication();
        $data->aop_application_id = strip_tags($request['aop_application_id']);
        $data->user_id = strip_tags($request['user_id']);
        $data->division_chief_id = strip_tags($request['division_chief_id']);
        $data->budget_officer_id = strip_tags($request['budget_officer_id']);
        $data->ppmp_total = strip_tags($request['ppmp_total']);
        $data->remarks = strip_tags($request['remarks']);
        $data->save();

        return response()->json([
            'data' => new PpmpApplicationResource($data),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ],
            'message' => $this->getMetadata('post'),
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(PpmpApplication $ppmpApplication)
    {
        return response()->json(new PpmpApplicationResource($ppmpApplication), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PpmpApplicationRequest $request, PpmpApplication $ppmpApplication)
    {
        $data = $request->all();
        $ppmpApplication->update($data);

        return response()->json([
            'data' => new PpmpApplicationResource($data),
            'pagination' => [
                'current_page' => $ppmpApplication->currentPage(),
                'last_page' => $ppmpApplication->lastPage(),
                'per_page' => $ppmpApplication->perPage(),
                'total' => $ppmpApplication->total(),
            ],
            'message' => $this->getMetadata('put'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PpmpApplication $ppmpApplication)
    {
        // Check if the record exists
        if (!$ppmpApplication) {
            return response()->json([
                'message' => 'Record not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Perform the deletion
        $ppmpApplication->delete();

        return response()->json([
            'pagination' => [
                'current_page' => $ppmpApplication->currentPage(),
                'last_page' => $ppmpApplication->lastPage(),
                'per_page' => $ppmpApplication->perPage(),
                'total' => $ppmpApplication->total(),
            ],
            'message' => $this->getMetadata('delete'),
        ], Response::HTTP_OK);
    }
}
