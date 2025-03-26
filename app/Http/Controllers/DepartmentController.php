<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DepartmentController extends Controller
{
    private $is_development;

    private $module = 'departments';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }

    private function cleanDepartmentData(array $data): array
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
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?department_id=[primary-key]",
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
        $department_id = $request->query('department_id') ?? null;

        if ($department_id) {
            $department = Department::find($department_id);

            if (!$department) {
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get', [])
                ]);
            }

            return response()->json([
                'data' => $department,
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

        $query = Department::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        if ($mode === 'selection') {
            $departments = $query->select('id', 'name', 'code')->get();
            return response()->json([
                'data' => $departments,
                'metadata' => $this->getMetadata('get', [])
            ], 200);
        }

        $total = $query->count();
        $total_page = ceil($total / $per_page);

        $departments = $query->skip(($page - 1) * $per_page)
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
            'data' => $departments,
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
        $base_message = "Successfully created department";

        // Bulk Insert
        if ($request->departments !== null || $request->departments > 1) {
            $existing_departments = [];
            $existing_items = Department::whereIn('name', collect($request->departments)->pluck('name'))
                ->get(['name'])->toArray();

            // Convert existing items into a searchable format
            $existing_names = array_column($existing_items, 'name');

            if(!empty($existing_items)){
                $existing_departments = Department::whereIn("name", $existing_names)->get();
            }

            foreach ($request->departments as $item) {
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
                    'data' => $existing_departments,
                    'message' => "Failed to bulk insert all departments already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Department::insert($cleanData);

            $latest_departments = Department::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_departments) > 1 ? $base_message."s record" : $base_message." record.";

            return response()->json([
                "data" => $latest_departments,
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT, DELETE]",
                    "duplicate_items" => $existing_departments
                ]
            ], Response::HTTP_CREATED);
        }
        
        // Single insert
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'nullable|string|max:50|unique:departments,code',
        ]);
        
        $department = Department::create($this->cleanDepartmentData($validated));
        
        return response()->json([
            'data' => $department,
            'message' => $base_message,
            'metadata' => $this->getMetadata('post', $department->toArray())
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return response()->json([
            'data' => $department,
            'metadata' => $this->getMetadata('get', [])
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'nullable|string|max:50|unique:departments,code,' . $department->id,
        ]);
        
        $department->update($this->cleanDepartmentData($validated));
        
        return response()->json([
            'data' => $department,
            'message' => 'Department updated successfully',
            'metadata' => $this->getMetadata('put', [])
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();
        
        return response()->json([
            'message' => 'Department deleted successfully',
            'metadata' => [
                'methods' => ['DELETE']
            ]
        ], Response::HTTP_OK);
    }
}
