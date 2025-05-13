<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "HttpRequestHelper",
    description: "Helper for making external API requests",
    properties: [
        new OA\Property(property: "base_url", type: "string"),
        new OA\Property(property: "timeout", type: "integer")
    ]
)]
class HttpRequestHelper
{
    public static function forwardRequestToExternalApi($endpoint, $method = 'GET', $data = [], $headers = [])
    {
        $default = env("UMIS_DOMAIN") ?? "https://umis.zcmc.online/api/";
        $externalApiUrl =  $default. ltrim($endpoint, '/');

        // Special handling for auth endpoint
        if ($endpoint === 'auth-with-session-id' && isset($data['session_id'])) {
            $externalApiUrl .= '?session_id=' . urlencode($data['session_id']);
            $data = []; // Clear data to avoid sending it twice
        }

        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('API_KEY') // Use API KEY from umis
        ];

        $headers = array_merge($defaultHeaders, $headers);

        $response = Http::withHeaders($headers)->{$method}($externalApiUrl, $data);

        return $response;
    }
}