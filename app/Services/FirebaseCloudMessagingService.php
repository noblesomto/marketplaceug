<?php
// app/Services/FirebaseCloudMessagingService.php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class FirebaseCloudMessagingService
{
    protected Client $client;
    protected ?string $serverKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->serverKey = config('services.fcm.server_key');
    }

    /**
     * Send notification to multiple device tokens
     */
    public function sendToTokens(array $tokens, array $notification, array $data = []): bool
    {
        if (empty($tokens)) {
            Log::warning('FCM: No tokens provided');
            return false;
        }

        if (empty($this->serverKey)) {
            Log::error('FCM: Server key not configured');
            return false;
        }

        $payload = [
            'registration_ids' => $tokens,
            'notification' => array_merge([
                'sound' => 'default',
                'badge' => 1,
            ], $notification),
            'data' => $data,
            'priority' => 'high',
            'content_available' => true,
        ];

        try {
            $response = $this->client->post('https://fcm.googleapis.com/fcm/send', [
                'headers' => [
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('FCM Response', [
                'success' => $result['success'] ?? 0,
                'failure' => $result['failure'] ?? 0,
            ]);

            // Handle invalid tokens
            if (isset($result['results'])) {
                $this->handleInvalidTokens($tokens, $result['results']);
            }

            return ($result['success'] ?? 0) > 0;

        } catch (GuzzleException $e) {
            Log::error('FCM Guzzle Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('FCM Error', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Mark invalid tokens as inactive
     */
    protected function handleInvalidTokens(array $tokens, array $results): void
    {
        foreach ($results as $index => $result) {
            if (isset($result['error'])) {
                $token = $tokens[$index] ?? null;

                if (!$token) continue;

                // Errors that indicate invalid/expired tokens
                $invalidErrors = [
                    'InvalidRegistration',
                    'NotRegistered',
                    'MismatchSenderId',
                ];

                if (in_array($result['error'], $invalidErrors)) {
                    \App\Models\DeviceToken::where('token', $token)
                        ->update(['is_active' => false]);

                    Log::info("FCM: Marked token as inactive", [
                        'token' => substr($token, 0, 20) . '...',
                        'error' => $result['error'],
                    ]);
                }
            }
        }
    }

    /**
     * Send notification to a single token
     */
    public function sendToToken(string $token, array $notification, array $data = []): bool
    {
        return $this->sendToTokens([$token], $notification, $data);
    }
}
