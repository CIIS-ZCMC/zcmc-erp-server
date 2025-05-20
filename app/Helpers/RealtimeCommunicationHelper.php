<?php

namespace App\Helpers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class RealtimeCommunicationHelper
{
    private static string $SOCKET_PRODUCTION_URL = "https://socket.zcmc.online/";
    private static string $SOCKET_URL = "http://192.168.36.158:3025/";
    private static string $BASE_PATH = "pr-monitoring";
    private static string $NOTIFICATION = "notification";
    private static string $EMAIL = "emails";

    /**
     * Summary of emit
     *
     * @param string $targetSocketEndpointBaseOnTableRecord
     * @param array $newRegisteredData
     * @return Response
     */
    public static function emit(string $targetSocketEndpointBaseOnTableRecord, array $newRegisteredData): Response
    {
        return Http::post(self::$SOCKET_URL . $targetSocketEndpointBaseOnTableRecord, $newRegisteredData)->throw();
    }

    /**
     * Summary of emitNewTransactionRecord
     *
     * @param $targetSocketEndpointBaseOnTableRecord
     * @param $newRegisteredData
     * @return Response
     */
    public static function emitNewTransactionRecord($targetSocketEndpointBaseOnTableRecord, $newRegisteredData): Response
    {
        return self::emit(self::$BASE_PATH, [
            "data" => $newRegisteredData,
            "event" => self::$BASE_PATH . '-' . $targetSocketEndpointBaseOnTableRecord
        ]);
    }
}
