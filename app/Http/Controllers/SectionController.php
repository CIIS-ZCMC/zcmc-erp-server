<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Helpers\PaginationHelper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\SectionResource;

class SectionController extends Controller
{
    private $is_development;
    private $module = 'sections';

    public function __construct()
    {
        $this->is_development = env("APP_DEBUG", true);
    }
    
    private function cleanSectionData(array $data)
    {
        return [
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
        ];
    }
    
    private function getMetadata($method, array $data = [])
    {
        if (strtolower($method) === 'get') {
            $metadata['methods'] = ["GET, POST, PUT, DELETE"];
            $metadata['modes'] = ['selection', 'pagination'];

            if ($this->is_development) {
                $metadata['urls'] = [
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?section_id=[primary-key]",
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
        $section_id = $request->query('section_id') ?? null;

        if ($section_id) {
            $section = Section::find($section_id);

            if (!$section) {
                return response()->json([
                    'message' => "No record found.",
                    "metadata" => $this->getMetadata('get', [])
                ]);
            }

            return response()->json([
                'data' => new SectionResource($section),
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

        $query = Section::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
        }

        if ($mode === 'selection') {
            $sections = $query->select('id', 'name', 'code')->get();
            return response()->json([
                'data' => new SectionResource($sections),
                'metadata' => $this->getMetadata('get', [])
            ], 200);
        }

        $total = $query->count();
        $total_page = ceil($total / $per_page);

        $sections = $query->skip(($page - 1) * $per_page)
            ->take($per_page)
            ->get();

        $pagination = [];

        // Previous page
        $pagination[] = [
            'title' => 'previous',
            'link' => $page > 1 ? url("/api/sections?page=" . ($page - 1) . "&per_page={$per_page}" . ($search ? "&search={$search}" : "")) : null,
            'is_active' => false
        ];

        // Page numbers
        for ($i = 1; $i <= $total_page; $i++) {
            $pagination[] = [
                'title' => $i,
                'link' => url("/api/sections?page={$i}&per_page={$per_page}" . ($search ? "&search={$search}" : "")),
                'is_active' => $i == $page
            ];
        }

        // Next page
        $pagination[] = [
            'title' => 'next',
            'link' => $page < $total_page ? url("/api/sections?page=" . ($page + 1) . "&per_page={$per_page}" . ($search ? "&search={$search}" : "")) : null,
            'is_active' => $page < $total_page
        ];

        return response()->json([
            'data' => new SectionResource($sections),
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
        $base_message = "Successfully created section";

        // Bulk Insert
        if ($request->sections !== null || $request->sections > 1) {
            $existing_sections = [];
            $existing_items = Section::whereIn('name', collect($request->sections)->pluck('name'))
                ->get(['name'])->toArray();

            // Convert existing items into a searchable format
            $existing_names = array_column($existing_items, 'name');

            if(!empty($existing_items)){
                $existing_sections = Section::whereIn("name", $existing_names)->get();
            }

            foreach ($request->sections as $item) {
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
                    'data' => new SectionResource($existing_sections),
                    'message' => "Failed to bulk insert all sections already exist.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            Section::insert($cleanData);

            $latest_sections = Section::orderBy('id', 'desc')
                ->limit(count($cleanData))->get()
                ->sortBy('id')->values();

            $message = count($latest_sections) > 1 ? $base_message."s record" : $base_message." record.";

            return response()->json([
                "data" => new SectionResource($latest_sections),
                "message" => $message,
                "metadata" => [
                    "methods" => "[GET, POST, PUT, DELETE]",
                    "duplicate_items" => $existing_sections
                ]
            ], Response::HTTP_CREATED);
        }
        
        // Single insert
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sections,name',
            'code' => 'nullable|string|max:50|unique:sections,code',
        ]);
        
        $section = Section::create($this->cleanSectionData($validated));
        
        return response()->json([
            'data' => new SectionResource($section),
            'message' => $base_message,
            'metadata' => $this->getMetadata('post', $section->toArray())
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
    {
        return response()->json([
            'data' => new SectionResource($section),
            'metadata' => $this->getMetadata('get', $section->toArray())
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sections,name,' . $section->id,
            'code' => 'nullable|string|max:50|unique:sections,code,' . $section->id,
        ]);
        
        $section->update($this->cleanSectionData($validated));
        
        return response()->json([
            'data' => new SectionResource($section),
            'metadata' => $this->getMetadata('put', $section->toArray())
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        $section->delete();
        
        return response()->json([
            'message' => 'Section deleted successfully',
            'metadata' => $this->getMetadata('delete', $section->toArray())
        ], Response::HTTP_OK);
    }
}
