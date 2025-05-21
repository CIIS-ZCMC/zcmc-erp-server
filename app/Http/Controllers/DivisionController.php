<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\DivisionResource;

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

            $metadata["fields"] = ["type"];
        }

        return $metadata;
    }
    public function import(Request $request)
    {
        return response()->json(['message' => "COMPLETE THIS ENDPOINT"], Response::HTTP_PARTIAL_CONTENT);
    }

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
                'data' => new DivisionResource($division),
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
                'data' => new DivisionResource($divisions),
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
            'data' => new DivisionResource($divisions),
            'metadata' => [
                'pagination' => $pagination,
                'page' => $page,
                'total_page' => $total_page
            ] + $this->getMetadata('get', [])
        ], 200);
    }

    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name,' . $division->id,
            'code' => 'nullable|string|max:50|unique:divisions,code,' . $division->id,
        ]);

        $division->update($this->cleanDivisionData($validated));

        return response()->json([
            'data' => new DivisionResource($division),
            'message' => 'Division updated successfully',
            'metadata' => $this->getMetadata('put', [])
        ], Response::HTTP_OK);
    }

    public function destroy(Division $division)
    {
        $division->delete();

        return response()->json([
            'message' => 'Division deleted successfully',
            'metadata' => $this->getMetadata('delete', $division->toArray())
        ], Response::HTTP_OK);
    }
}
