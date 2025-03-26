<?php

namespace App\Http\Controllers;

use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitController extends Controller
{
    private $is_development;

    private $module = 'units';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanUnitData(array $data): array
    {
        $cleanData = [];

        if (isset($data['name'])) {
            $cleanData['name'] = strip_tags($data['name']);
        }

        if (isset($data['head_id'])) {
            $cleanData['head_id'] = (int)$data['head_id'];
        }

        return $cleanData;
    }

    protected function getMetadata($method, array $data): array
    {
        if ($method === 'get') {
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?unit_id=[primary-key]",
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
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2"
                ];
                $metadata['fields'] = ["type"];
            }

            return $metadata;
        }

        $metadata = ['methods' => ["GET, PUT, DELETE"]];

        if ($this->is_development) {
            $metadata["urls"] = [
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id=1",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?id[]=1&id[]=2",
                env("SERVER_DOMAIN") . "/api/" . $this->module . "?query[target_field]=value"
            ];

            $metadata["fields"] =  ["type"];
        }

        return $metadata;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->query('page') > 0 ? $request->query('page') : 1;
        $per_page = $request->query('per_page');
        $mode = $request->query('mode') ?? 'pagination';
        $search = $request->query('search');
        $last_id = $request->query('last_id') ?? 0;
        $last_initial_id = $request->query('last_initial_id') ?? 0;
        $page_item = $request->query('page_item') ?? 0;
        $unit_id = $request->query('unit_id') ?? null;

        if ($unit_id) {
            $unit = Unit::find($unit_id);

            if (!$unit) {
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get', [])
                ]);
            }

            return response()->json([
                'data' => new UnitResource($unit),
                "metadata" => $this->getMetadata('get', [])
            ], 200);
        }

        if ($page < 0 || $per_page < 0) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "Invalid value of parameters",
                    "metadata" => $this->getMetadata('get', [])
                ];
            }

            return response()->json($response, 422);
        }

        if (!$page && !$per_page) {
            $response = ["message" => "Invalid request."];

            if ($this->is_development) {
                $response = [
                    "message" => "No parameters found.",
                    "metadata" => $this->getMetadata('get', [])
                ];
            }

            return response()->json($response, 422);
        }

        $query = Unit::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if ($mode === 'selection') {
            $units = $query->select('id', 'name')->get();
            return response()->json([
                'data' => new UnitResource($units),
                'metadata' => $this->getMetadata('get', [])
            ], 200);
        }

        $total = $query->count();
        $total_page = ceil($total / $per_page);

        $units = $query->skip(($page - 1) * $per_page)
            ->take($per_page)
            ->get();

        $pagination = [];

        // Previous page
        $pagination[] = [
            'title' => 'previous',
            'link' => $page > 1 ? url("/api/{$this->module}?page=" . ($page - 1) . "&per_page={$per_page}" . ($search ? "&search={$search}" : "")) : null,
            'is_active' => false
        ];

        // Page numbers
        for ($i = 1; $i <= $total_page; $i++) {
            $pagination[] = [
                'title' => $i,
                'link' => url("/api/{$this->module}?page={$i}&per_page={$per_page}" . ($search ? "&search={$search}" : "")),
                'is_active' => $i == $page
            ];
        }

        // Next page
        $pagination[] = [
            'title' => 'next',
            'link' => $page < $total_page ? url("/api/{$this->module}?page=" . ($page + 1) . "&per_page={$per_page}" . ($search ? "&search={$search}" : "")) : null,
            'is_active' => $page < $total_page
        ];

        return response()->json([
            'data' => new UnitResource($units),
            'metadata' => [
                'pagination' => $pagination,
                'page' => $page,
                'total_page' => $total_page
            ] + $this->getMetadata('get', [])
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $base_message = "Successfully created unit";

        // Bulk Insert
        if ($request->units !== null || $request->units > 1) {
            $existing_units = [];
            $existing_items = Unit::whereIn('name', collect($request->units)->pluck('name'))
                ->get(['name'])->toArray();

            // Convert existing items into a searchable format
            $existing_names = array_column($existing_items, 'name');

            if(!empty($existing_items)){
                $existing_units = Unit::whereIn("name", $existing_names)->get();
            }

            foreach ($request->units as $item) {
                if (!in_array($item['name'], $existing_names)) {
                    $cleanData[] = [
                        "name" => strip_tags($item['name']),
                        "description" => isset($item['description']) ? strip_tags($item['description']) : null,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }
            }

            if (empty($cleanData) && count($existing_items) > 0) {
                return response()->json([
                    'data' => new UnitResource($existing_units),
                    'message' => "Failed to bulk insert all units already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Unit::insert($cleanData);

            $latest_units = Unit::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_units) > 1 ? $base_message."s record" : $base_message." record.";

            return response()->json([
                "data" => new UnitResource($latest_units),
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT, DELETE]",
                    "duplicate_items" => $existing_units
                ]
            ], Response::HTTP_CREATED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        return response()->json([
            'data' => new UnitResource($unit),
            'metadata' => $this->getMetadata('get', [])
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $cleanData = $this->cleanUnitData($request->all());
        
        $unit->update($cleanData);
        
        return response()->json([
            'data' => new UnitResource($unit),
            'message' => 'Unit updated successfully',
            'metadata' => $this->getMetadata('put', [])
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        $unit->delete();
        
        return response()->json([
            'message' => 'Unit deleted successfully',
            'metadata' => $this->getMetadata('delete', $unit->toArray())
        ], Response::HTTP_OK);
    }
}
