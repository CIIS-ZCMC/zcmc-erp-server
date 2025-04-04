<?php

namespace App\Http\Controllers;

use App\Http\Resources\PpmpItemResource;
use App\Models\AopApplication;
use App\Models\PpmpItem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PpmpItemController extends Controller
{
    private $is_development;

    private $module = 'ppmp_items';

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
                    env("SERVER_DOMAIN") . "/api/" . $this->module . "?ppmp_item_id=[primary-key]",
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

            $metadata["fields"] = ["type"];
        }

        return $metadata;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //paginate display 10 data per page
        $ppmp_item = PpmpItem::whereNull('deleted_at')->paginate(10);

        if (!$ppmp_item) {
            return response()->json([
                'message' => "No record found.",
                "metadata" => $this->getMetadata('get')
            ]);
        }

        return response()->json([
            'data' => PpmpItemResource::collection($ppmp_item),
            'message' => $this->getMetadata('get')
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PpmpItem $ppmpItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PpmpItem $ppmpItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PpmpItem $ppmpItem)
    {
        //
    }
}
