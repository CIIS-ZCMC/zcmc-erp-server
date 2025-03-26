<?php

use Illuminate\Support\Facades\Http;

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