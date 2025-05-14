<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
    /**
     * Forwards a request to an external API and returns the response.
     *
     * @param string $endpoint The endpoint to call, relative to the UMIS API URL
     * @param string $method The HTTP method to use (default: GET)
     * @param array $data The data to send with the request
     * @param array $headers Additional headers to send with the request
     *
     * @return \Illuminate\Http\Client\Response The response from the external API
     */
    public static function forwardRequestToExternalApi($endpoint, $method = 'GET', $data = [], $headers = [])
    {
        $default = env("UMIS_API_URL") . "/" ?? "http://192.168.9.243:8010/api";
        $externalApiUrl =  $default . ltrim($endpoint, '/');

        // Special handling for auth endpoint
        if ($endpoint === 'auth-with-session-id' && isset($data['session_id'])) {
            $externalApiUrl .= '?session_id=' . urlencode($data['session_id']);
            $data = []; // Clear data to avoid sending it twice
        }

        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('UMIS_API_KEY') // Use API KEY from umis
        ];

        $headers = array_merge($defaultHeaders, $headers);

        Log::info('Making external API request', [
            'url' => $externalApiUrl,
            'method' => $method,
            'headers' => array_diff_key($headers, ['Authorization' => '']), // Don't log auth token
            'data' => $data
        ]);

        $response = Http::withHeaders($headers)->{$method}($externalApiUrl, $data);

        Log::info('External API response received', [
            'url' => $externalApiUrl,
            'status' => $response->status(),
            'success' => $response->successful()
        ]);

        return $response;
    }
}
