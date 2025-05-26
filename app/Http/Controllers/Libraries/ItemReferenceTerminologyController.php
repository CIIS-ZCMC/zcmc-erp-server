<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemReferenceTerminologyRequest;
use App\Http\Resources\ItemReferenceTerminologyResource;
use App\Models\ItemReferenceTerminology;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ItemReferenceTerminologyController extends Controller
{
    protected $methods = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ItemReferenceTerminologyResource::collection(ItemReferenceTerminology::all())
            ->additional([
                'meta' => [
                    'methods' => $this->methods
                ],
                'message' => 'Successfully retrieve list of variants.'
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ItemReferenceTerminologyRequest $request)
    {
        $name =strip_tags( $request->name);
        $code = strip_tags($request->code);

        $new_variant = ItemReferenceTerminology::create([
            'name' => $name,
            'code' => $code
        ]);

        return (new ItemReferenceTerminologyResource($new_variant))
            ->additional([
                'meta' => [
                    'methods' => $this->methods
                ]
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ItemReferenceTerminologyRequest $request, ItemReferenceTerminology $variant)
    {
        $variant->update($request->all());

        return (new ItemReferenceTerminologyResource($variant))
            ->additional([
                'meta' => [
                    'methods' => $this->methods
                ],
                'message' => 'Successfully update variant'
            ]);
    }

    public function trash(Request $request)
    {
        $search = $request->query('search');

        $query = ItemReferenceTerminology::onlyTrashed();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', "%{$search}%");
        }
        
        return ItemReferenceTerminologyResource::collection($query->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieved deleted records."
            ]);
    }

    public function restore($id, Request $request)
    {
        ItemReferenceTerminology::withTrashed()->where('id', $id)->restore();

        return (new ItemReferenceTerminologyResource(ItemReferenceTerminology::find($id)))
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Succcessfully restore record."
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemReferenceTerminology $variant)
    {
        $variant->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
