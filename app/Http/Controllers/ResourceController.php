<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResourceRequest;
use App\Models\Resource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends Controller
{
    public function index()
    {
        //
    }

    public function store(ResourceRequest $request)
    {
        $data = new Resource();
        $data->activity_id = $request->activity_id;
        $data->item_id = $request->item_id;
        $data->purchase_type_id = $request->purchase_type_id;
        $data->quantity = $request->quantity;
        $data->expense_class = $request->expense_class;
        $data->save();

        return response()->json([
            'message' => 'Resource created successfully',
            'data' => $data
        ], Response::HTTP_CREATED);
    }

    public function show(Resource $resource)
    {
        //
    }

    public function update(Request $request, Resource $resource)
    {
        //
    }

    public function destroy(Resource $resource)
    {
        //
    }
}
