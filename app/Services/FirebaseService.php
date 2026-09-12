<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase/firebase_credentials.json');

        if (!file_exists($credentialsPath)) {
            Log::error('Firebase credentials file missing at: ' . $credentialsPath);
            $this->messaging = null;
            return;
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount($credentialsPath);

            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase Factory initialization error: ' . $e->getMessage());
            $this->messaging = null;
        }
    }

    public function sendNotification($tokens, $title, $body)
    {
        $results = [];

        if (!$this->messaging) {
            foreach ($tokens as $token) {
                $results[] = [
                    'token' => $token,
                    'status' => 'failed',
                    'error' => 'Firebase Messaging not initialized (check credentials)',
                ];
            }
            return $results;
        }

        $notification = Notification::create($title, $body);
        $message = CloudMessage::new()->withNotification($notification);

        Log::info('Firebase Sending Payload:', [
            'title' => $title,
            'body' => $body,
            'tokens_count' => count($tokens)
        ]);

        foreach ($tokens as $token) {
            try {
                $response = $this->messaging->send($message->withChangedTarget('token', $token));

                Log::info('Firebase Send Success:', [
                    'token' => $token,
                    'response' => $response
                ]);

                $results[] = [
                    'token' => $token,
                    'status' => 'success',
                    'response' => $response,
                ];
            } catch (MessagingException $e) {
                Log::error('Firebase Messaging Error: ' . $e->getMessage(), [
                    'token' => $token,
                    'error_code' => $e->getCode(),
                    'trace' => $e->getTraceAsString()
                ]);

                $results[] = [
                    'token' => $token,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
