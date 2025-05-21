<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;
use App\Http\Requests\SnomedRequest;
use App\Http\Resources\SnomedResource;
use App\Models\Snomed;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SnomedController extends Controller
{
    protected $methods = ['GET', 'POST', 'PUT', 'DELETE'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return SnomedResource::collection(Snomed::all())
            ->additional([
                'meta' => [
                    'methods' 
                ],
                'message' => "Successfully fetch snomed codes"
            ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SnomedRequest $request)
    {
        $code = strip_tags($request->code);

        $exist = Snomed::where('code', $code)->first();

        if($exist){
            return response()->json(['message' => 'Snomed code already registered.'], Response::HTTP_BAD_REQUEST);
        }

        $new_snomed = Snomed::create(['code' => $code]);

        return (new SnomedResource($new_snomed))
            ->additional([
                'meta' => [
                    'methods' => $this->methods
                ],
                'message' => 'Successfully register snomed code.'
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SnomedRequest $request, Snomed $snomed)
    {
        $snomed->update(['code' => $request->code]);

        return (new SnomedResource($snomed))
            ->additional([
                'meta' => [
                    'methods' => $this->methods
                ],
                'message' => 'Successfully update snomed code.'
            ]);
    }

    public function trashbin(Request $request)
    {
        $search = $request->query('search');

        $query = Snomed::onlyTrashed();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
        }
        
        return SnomedResource::collection(Snomed::onlyTrashed()->get())
            ->additional([
                "meta" => [
                    "methods" => $this->methods
                ],
                "message" => "Successfully retrieved deleted records."
            ]);
    }

    public function restore($id, Request $request)
    {
        Snomed::withTrashed()->where('id', $id)->restore();

        return (new SnomedResource(Snomed::find($id)))
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
    public function destroy(Snomed $snomed)
    {
        $snomed->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
