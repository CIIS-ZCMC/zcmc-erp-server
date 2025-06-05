<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;

use App\Http\Resources\TerminologyResource;
use App\Models\ItemCategory;
use App\Models\ItemReferenceTerminology;
use App\Models\TerminologyCategory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TerminologyController extends Controller
{
    public function index(Request $request)
    {
        $terminologies = TerminologyCategory::all();
        
        return TerminologyResource::collection($terminologies)
            ->additional([
                'meta' => [
                    'methods' => ['GET']
                ],
                'message' => "Retrievel list of terminologies"
            ]);
    }

    public function store(Request $request)
    {
        $category_id = $request->category_id;
        $referrence_terminology_id = $request->referrence_terminology_id;

        $category = ItemCategory::find($category_id);
        $referrence_terminology = ItemReferenceTerminology::find($referrence_terminology_id);

        if(!$category){
            return response()->json(['message' => "Invalid category"], Response::HTTP_BAD_REQUEST);
        }

        if(!$referrence_terminology){
            return response()->json(['message' => "Invalid referrence terminology"], Response::HTTP_BAD_REQUEST);
        }

        $data = [
            'name' => $referrence_terminology['system'].'-'.$referrence_terminology['code'],
            'category_id' => $category_id,
            'referrence_terminology_id' => $referrence_terminology_id
        ];

        $terminology = TerminologyCategory::create($data);

        return (new TerminologyResource($terminology))
            ->additional([
                'meta' => [
                    'methods' => ['GET','POST']
                ],
                'message' => "Successfully create terminology."
            ]);
    }
}
