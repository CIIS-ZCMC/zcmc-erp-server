<?php

namespace App\Helpers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Client\Response;

class RealtimeCommunicationHelper
{
    private static string $SOCKET_PRODUCTION_URL;
    private static string $SOCKET_DEVELOPMENT_URL;
    private static string $BASE_PATH = "erp-notifications";
    private static string $NOTIFICATION_EVENT = "erp-notification";
    private static string $EMAIL_EVENT = "erp-email";
    private static string $AOP_EVENT = "aop-notification";

    /**
     * Initialize the socket URLs from environment variables
     */
    private static function initializeConfig()
    {
        // Use environment variables with fallbacks
        self::$SOCKET_PRODUCTION_URL = env('SOCKET_PRODUCTION_URL', 'https://socket.zcmc.online/');
        self::$SOCKET_DEVELOPMENT_URL = env('SOCKET_DEVELOPMENT_URL', 'http://localhost:3025/');
    }

    /**
     * Send data to a socket endpoint
     *
     * @param string $targetSocketEndpoint
     * @param array $data
     * @return Response
     */
    public static function emit(string $targetSocketEndpoint, array $data): Response
    {
        self::initializeConfig();

        // Use production URL in production environment
        $socketUrl = Config::get('app.env') === 'production'
            ? self::$SOCKET_PRODUCTION_URL
            : self::$SOCKET_DEVELOPMENT_URL;

        try {
            $response = Http::post($socketUrl . $targetSocketEndpoint, $data);

            Log::info('Socket emit successful', [
                'endpoint' => $targetSocketEndpoint,
                'status' => $response->status()
            ]);

            return $response->throw();
        } catch (ConnectionException $e) {
            // Log the connection error but don't throw to prevent app disruption
            Log::error('Socket connection error: ' . $e->getMessage(), [
                'endpoint' => $targetSocketEndpoint,
                'exception' => $e->getMessage()
            ]);

            // Return a fake response to prevent app disruption
            return new Response(new \GuzzleHttp\Psr7\Response(503));
        } catch (\Exception $e) {
            Log::error('Socket emit error: ' . $e->getMessage(), [
                'endpoint' => $targetSocketEndpoint,
                'exception' => $e->getMessage()
            ]);

            // Return a fake response to prevent app disruption
            return new Response(new \GuzzleHttp\Psr7\Response(500));
        }
    }

    /**
     * Emit a notification to the socket server
     *
     * @param int $userId The user ID to send the notification to
     * @param array $notificationData The notification data
     * @return Response
     */
    public static function emitNotification(int $userId, array $notificationData): Response
    {
        return self::emit(self::$BASE_PATH, [
            "data" => $notificationData,
            "event" => self::$NOTIFICATION_EVENT,
            "userId" => $userId
        ]);
    }

    /**
     * Emit an AOP application update to the socket server
     *
     * @param int $aopId The AOP application ID
     * @param array $aopData The AOP application data
     * @return Response
     */
    public static function emitAopUpdate(int $aopId, array $aopData): Response
    {
        return self::emit(self::$BASE_PATH, [
            "data" => $aopData,
            "event" => self::$AOP_EVENT,
            "aopId" => $aopId
        ]);
    }

    /**
     * Emit a notification to multiple users
     *
     * @param array $userIds Array of user IDs to notify
     * @param array $notificationData The notification data
     * @return Response
     */
    public static function emitMultiUserNotification(array $userIds, array $notificationData): Response
    {
        return self::emit(self::$BASE_PATH, [
            "data" => $notificationData,
            "event" => self::$NOTIFICATION_EVENT,
            "userIds" => $userIds
        ]);
    }

    /**
     * Emit an email notification event
     *
     * @param string $email The recipient email address
     * @param array $emailData The email data
     * @return Response
     */
    public static function emitEmailNotification(string $email, array $emailData): Response
    {
        return self::emit(self::$BASE_PATH, [
            "data" => $emailData,
            "event" => self::$EMAIL_EVENT,
            "email" => $email
        ]);
    }
}
