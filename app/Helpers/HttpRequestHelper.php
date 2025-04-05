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
        $default = env("UMIS_API") ?? "https://umis.zcmc.online/api/";

        $externalApiUrl =  $default. ltrim($endpoint, '/');

        $defaultHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . env('EXTERNAL_API_TOKEN') // Use API KEY from umis
        ];

        $headers = array_merge($defaultHeaders, $headers);

        $response = Http::withHeaders($headers)->{$method}($externalApiUrl, $data);

        return $response->json();
    }
}