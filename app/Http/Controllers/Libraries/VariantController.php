<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;
use App\Http\Requests\VariantRequest;
use App\Http\Resources\VariantResource;
use App\Models\Variant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VariantController extends Controller
{
    protected $methods = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return VariantResource::collection(Variant::all())
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
    public function store(VariantRequest $request)
    {
        $name =strip_tags( $request->name);
        $code = strip_tags($request->code);

        $new_variant = Variant::create([
            'name' => $name,
            'code' => $code
        ]);

        return (new VariantResource($new_variant))
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
    public function update(VariantRequest $request, Variant $variant)
    {
        $variant->update($request->all());

        return (new VariantResource($variant))
            ->additional([
                'meta' => [
                    'methods' => $this->methods
                ],
                'message' => 'Successfully update variant'
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variant $variant)
    {
        $variant->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
