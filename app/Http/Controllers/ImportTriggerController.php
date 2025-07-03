<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ImportTriggerController extends Controller
{
    public function triggerImport(Request $request)
    {
        $secret = $request->header('X-UMIS-SECRET');

        if ($secret !== env('UMIS_SECRET')) {
            \Log::warning('❌ Invalid UMIS secret received', [
                'received' => $secret,
                'expected' => env('UMIS_SECRET'),
            ]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        Artisan::call('import:all');
        return response()->json(['message' => 'Import triggered successfully']);
    }
}
