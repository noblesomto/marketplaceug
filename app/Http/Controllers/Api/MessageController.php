<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Advert;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use App\Events\NewMessageNotification;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Send a new message",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"advert_id", "receiver_id", "message_content"},
     *             @OA\Property(property="advert_id", type="integer", description="Advert ID"),
     *             @OA\Property(property="receiver_id", type="integer", description="Receiver user ID"),
     *             @OA\Property(property="message_content", type="string", description="Message content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Message")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_id' => 'required|integer|exists:adverts,id',
            'receiver_id' => 'required|integer|exists:users,user_id',
            'message_content' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();

        $message = Message::create([
            'advert_id' => $request->advert_id,
            'sender_id' => $user->user_id,
            'receiver_id' => $request->receiver_id,
            'message_content' => $request->message_content,
            'is_read' => false
        ]);

        // Broadcast events
        broadcast(new MessageSent($message))->toOthers();
        broadcast(new NewMessageNotification($message));

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/conversation/{advertId}/{receiverId}",
     *     summary="Get conversation between users for an advert",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="receiverId",
     *         in="path",
     *         required=true,
     *         description="Receiver user ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Conversation messages",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="messages", type="array", @OA\Items(ref="#/components/schemas/Message")),
     *                 @OA\Property(property="advert", ref="#/components/schemas/Advert"),
     *                 @OA\Property(property="receiver", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advert or user not found"
     *     )
     * )
     */
    public function getConversation($advertId, $receiverId)
    {
        $user = auth()->user();

        $advert = Advert::with('owner')->find($advertId);
        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $receiver = User::find($receiverId);
        if (!$receiver) {
            return response()->json([
                'success' => false,
                'message' => 'Receiver not found'
            ], 404);
        }

        $messages = Message::where(function ($query) use ($user, $receiver, $advertId) {
                $query->where('sender_id', $user->user_id)
                      ->where('receiver_id', $receiver->user_id)
                      ->where('advert_id', $advertId);
            })
            ->orWhere(function ($query) use ($user, $receiver, $advertId) {
                $query->where('sender_id', $receiver->user_id)
                      ->where('receiver_id', $user->user_id)
                      ->where('advert_id', $advertId);
            })
            ->orderBy('created_at')
            ->get();

        // Mark unread messages as read
        Message::where('receiver_id', $user->user_id)
            ->where('sender_id', $receiver->user_id)
            ->where('advert_id', $advertId)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages,
                'advert' => $advert,
                'receiver' => $receiver
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/advert/{advertId}",
     *     summary="Get all messages for an advert",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages for the advert",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Message"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getAdvertMessages($advertId)
    {
        $user = auth()->user();

        $messages = Message::with(['sender', 'receiver'])
            ->where('advert_id', $advertId)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->user_id)
                      ->orWhere('receiver_id', $user->user_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/conversations",
     *     summary="Get all user conversations",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of conversations",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Conversation"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getUserConversations(Request $request)
    {
        $user = auth()->user();

        $conversations = Message::select('advert_id', 'sender_id', 'receiver_id')
            ->selectRaw('MAX(created_at) as last_message_date')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->user_id)
                      ->orWhere('receiver_id', $user->user_id);
            })
            ->with(['advert', 'sender', 'receiver'])
            ->groupBy('advert_id', 'sender_id', 'receiver_id')
            ->orderBy('last_message_date', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/messages/unread/count",
     *     summary="Get count of unread messages",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unread message count",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="unread_count", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getUnreadCount()
    {
        $user = auth()->user();

        $unreadCount = Message::where('receiver_id', $user->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/messages/{messageId}/read",
     *     summary="Mark message as read",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="messageId",
     *         in="path",
     *         required=true,
     *         description="Message ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     )
     * )
     */
    public function markAsRead($messageId)
    {
        $user = auth()->user();

        $message = Message::where('id', $messageId)
            ->where('receiver_id', $user->user_id)
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found'
            ], 404);
        }

        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/messages/conversation/{advertId}/{userId}/read",
     *     summary="Mark all messages in conversation as read",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="advertId",
     *         in="path",
     *         required=true,
     *         description="Advert ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="Other user ID in conversation",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function markConversationAsRead($advertId, $userId)
    {
        $user = auth()->user();

        Message::where('advert_id', $advertId)
            ->where('sender_id', $userId)
            ->where('receiver_id', $user->user_id)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation marked as read'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/payments/{paymentId}/mark-delivered",
     *     summary="Mark payment as delivered",
     *     tags={"Payments"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="paymentId",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment marked as delivered",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function markAsDelivered($paymentId)
    {
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $payment->update([
            'buyer_status' => "delivered",
            'updated_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as delivered'
        ]);
    }
}
