<?php

namespace App\Http\Controllers\Libraries;

use App\Http\Controllers\Controller;

use App\Http\Resources\TerminologyResource;
use App\Models\TerminologyCategory;
use Illuminate\Http\Request;

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
}
