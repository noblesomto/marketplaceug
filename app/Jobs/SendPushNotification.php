<?php
// app/Jobs/SendPushNotification.php

namespace App\Jobs;

use App\Models\Message;
use App\Models\User;
use App\Services\FirebaseCloudMessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;

    protected User $user;
    protected Message $message;
    protected User $sender;

    public function __construct(User $user, Message $message, User $sender)
    {
        $this->user = $user;
        $this->message = $message;
        $this->sender = $sender;
    }

    public function handle(FirebaseCloudMessagingService $fcmService): void
    {
        // Check if user can receive notifications
        if (!$this->user->canReceivePushNotifications()) {
            Log::info('User cannot receive push notifications', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        // Get all active device tokens
        $tokens = $this->user->deviceTokens->pluck('token')->toArray();

        if (empty($tokens)) {
            Log::info('No device tokens found', [
                'user_id' => $this->user->id,
            ]);
            return;
        }

        // Prepare notification
        $messagePreview = mb_strlen($this->message->body) > 100
            ? mb_substr($this->message->body, 0, 100) . '...'
            : $this->message->body;

        $notification = [
            'title' => $this->sender->name,
            'body' => $messagePreview,
        ];

        // Add image if available
        if (!empty($this->sender->profile_image_url)) {
            $notification['image'] = $this->sender->profile_image_url;
        }

        $data = [
            'type' => 'new_message',
            'message_id' => (string) $this->message->id,
            'sender_id' => (string) $this->sender->user_id,
            'sender_name' => $this->sender->name,
            'conversation_id' => (string) ($this->message->conversation_id ?? ''),
            'timestamp' => $this->message->created_at->toIso8601String(),
            'click_action' => 'OPEN_CONVERSATION',
        ];

        // Send notification
        $result = $fcmService->sendToTokens($tokens, $notification, $data);

        Log::info('Push notification sent', [
            'user_id' => $this->user->id,
            'message_id' => $this->message->id,
            'token_count' => count($tokens),
            'success' => $result,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Push notification job failed', [
            'user_id' => $this->user->id,
            'message_id' => $this->message->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
