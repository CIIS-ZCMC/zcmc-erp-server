<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DivisionController extends Controller
{
    private $is_development;

    private $module = 'divisions';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanDivisionData(array $data): array
    {
        $cleanData = [];

        if (isset($data['name'])) {
            $cleanData['name'] = strip_tags($data['name']);
        }

        if (isset($data['code'])) {
            $cleanData['code'] = strip_tags($data['code']);
        }

        return $cleanData;
    }

    protected function getMetadata($method, array $data): array
    {
        if (strtolower($method) === 'get') {
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?division_id=[primary-key]",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?page={currentPage}&per_page={number_of_record_to_return}&search=value",
                ];
            }

            return $metadata;
        }

        if (strtolower($method) === 'put') {
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
        $division_id = $request->query('division_id') ?? null;

        if ($division_id) {
            $division = Division::find($division_id);

            if (!$division) {
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get', [])
                ]);
            }

            return response()->json([
                'data' => $division,
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

        $query = Division::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        if ($mode === 'selection') {
            $divisions = $query->select('id', 'name', 'code')->get();
            return response()->json([
                'data' => $divisions,
                'metadata' => $this->getMetadata('get', [])
            ], 200);
        }

        $total = $query->count();
        $total_page = ceil($total / $per_page);

        $divisions = $query->skip(($page - 1) * $per_page)
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
            'data' => $divisions,
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
        $base_message = "Successfully created division";

        // Bulk Insert
        if ($request->divisions !== null || $request->divisions > 1) {
            $existing_divisions = [];
            $existing_items = Division::whereIn('name', collect($request->divisions)->pluck('name'))
                ->get(['name'])->toArray();

            // Convert existing items into a searchable format
            $existing_names = array_column($existing_items, 'name');

            if(!empty($existing_items)){
                $existing_divisions = Division::whereIn("name", $existing_names)->get();
            }

            foreach ($request->divisions as $item) {
                if (!in_array($item['name'], $existing_names)) {
                    $cleanData[] = [
                        "name" => strip_tags($item['name']),
                        "code" => isset($item['code']) ? strip_tags($item['code']) : null,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                }
            }

            if (empty($cleanData) && count($existing_items) > 0) {
                return response()->json([
                    'data' => $existing_divisions,
                    'message' => "Failed to bulk insert all divisions already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Division::insert($cleanData);

            $latest_divisions = Division::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_divisions) > 1 ? $base_message."s record" : $base_message." record.";

            return response()->json([
                "data" => $latest_divisions,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT, DELETE]",
                    "duplicate_items" => $existing_divisions
                ]
            ], Response::HTTP_CREATED);
        }
        
        // Single insert
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name',
            'code' => 'nullable|string|max:50|unique:divisions,code',
        ]);
        
        $division = Division::create($this->cleanDivisionData($validated));
        
        return response()->json([
            'data' => $division,
            'message' => $base_message,
            'metadata' => $this->getMetadata('post', $division->toArray())
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {
        return response()->json([
            'data' => $division,
            'metadata' => $this->getMetadata('get', [])
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name,' . $division->id,
            'code' => 'nullable|string|max:50|unique:divisions,code,' . $division->id,
        ]);
        
        $division->update($this->cleanDivisionData($validated));
        
        return response()->json([
            'data' => $division,
            'message' => 'Division updated successfully',
            'metadata' => $this->getMetadata('put', [])
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $division->delete();
        
        return response()->json([
            'message' => 'Division deleted successfully',
            'metadata' => [
                'methods' => ['DELETE']
            ]
        ], Response::HTTP_OK);
    }
}
