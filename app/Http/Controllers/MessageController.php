<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\MessageImage;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\User;
use App\Models\Advert;
use App\Models\Payment;
use App\Models\BlockedUser;
use Carbon\Carbon;
use App\Events\NewMessageNotification;
use App\Helpers\FileUploadHelper;
use App\Models\ArchivedMessage;
use App\Jobs\SendPushNotification;
use App\Traits\HasUserSession;


class MessageController extends Controller
{
    use HasUserSession;


    // REMOVED: fetchMessages() - had dd() debug statement, replaced by showMessages()

    public function fetchMyMessages(Request $request, $id)
    {   
        $user_id = $request->session()->get('user_id');
        $message = DB::table('messages')
                ->join('users','messages.receiver_id', '=', 'users.user_id')
                ->where('messages.advert_id',$id)
                ->where('messages.receiver_id',$user_id)
                ->get();
        //dd($message);
        return ($message);
    }


    public function showMessages(Request $request, $advertId, $receiverId)
    {
        $title = "User Dashboard  - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $advert = Advert::with('owner')->findOrFail($advertId);
        $receiver = User::where('user_id', $receiverId)->first();
        $sender = User::where('user_id', $user_id)->first();
        $payment = Payment::where('advert_id', $advertId)->first();
        //dd($sender);
        
        $messages = Message::with('images')
            ->where(function ($query) use ($sender, $receiver, $advertId) {
                $query->where('sender_id', $sender->user_id)
                      ->where('receiver_id', $receiver->user_id)
                      ->where('advert_id', $advertId);
            })
            ->orWhere(function ($query) use ($sender, $receiver, $advertId) {
                $query->where('sender_id', $receiver->user_id)
                      ->where('receiver_id', $sender->user_id)
                      ->where('advert_id', $advertId);
            })
            ->orderBy('created_at')
            ->get();

        // Mark unread messages as read
        Message::where('receiver_id', $sender->user_id)
            ->where('sender_id', $receiver->user_id)
            ->where('advert_id', $advertId)
            ->update(['is_read' => true]);

            $isBlocked = $user->hasBlocked($receiver->user_id, $advert->id ?? null);

        return view('dashboard.chat', compact('title','messages', 'advert', 'receiver','user','count_ads','payment','isBlocked'));
    }

    public function sendMessage(Request $request, $advertId, $receiverId)
{
    $request->validate([
        'message'   => 'nullable|string|max:1000',
        'images.*'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:12048',
    ]);

    $senderId = $request->session()->get('user_id');

    // Check if sender is blocked
    $isBlocked = BlockedUser::where('blocker_id', $receiverId)
        ->where('blocked_id', $senderId)
        ->where(function($query) use ($advertId) {
            $query->where('advert_id', $advertId)
                  ->orWhereNull('advert_id');
        })
        ->exists();

    if ($isBlocked) {
        return response()->json([
            'success' => false,
            'message' => 'You cannot send messages to this user'
        ], 462);
    }

    // Create the message
    $message = new Message();
    $message->advert_id = $advertId;
    $message->sender_id = $senderId;
    $message->receiver_id = $receiverId;
    $message->message_content = $request->message;
    $message->is_read = false;
    $message->save();

    // Handle image uploads if present
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $imageFile) {
            $messageImage = MessageImage::create([
                'message_id' => $message->id,
            ]);

            // Add media - conversions will be created automatically
            // Since you're using nonQueued(), they'll be created immediately
            $messageImage->addMedia($imageFile)
                ->toMediaCollection('message_images');

            // Original file deletion is already handled in conversionCompleted() method
            // No need to manually delete here
        }
    }

    // Fire event
    event(new NewMessageNotification($message));
    try {
        $receiver = User::where('user_id', $receiverId)->first();
        $sender = User::where('user_id', $senderId)->first();
        if ($receiver && $sender) {
            SendPushNotification::dispatch($receiver, $message, $sender);
        }
    } catch (\Exception $e) {
        \Log::error('Failed to dispatch push notification: ' . $e->getMessage());
    }

    return redirect()->back()->with('success', 'Message sent successfully!');
}




    public function countUnreadMessages(Request $request)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $unreadCount = Message::where('receiver_id', $user->user_id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $unreadCount]);
    }

    public function mark_received($id)
    {   
        DB::table('payments')
            ->where('id', $id)
            ->update([
                'buyer_status'=> "delivered",
                'updated_at' => Carbon::now(),
            ]);
 
        return redirect()->back()->with('success', 'Ad Status Marked Delivered');
    
    }

    public function archive(Request $request)
    {
        //dd($request);
        $validated = $request->validate([
            'advert_id' => 'required|exists:adverts,id',
            'other_user_id' => 'required|exists:users,user_id',
        ]);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect('/login');
        }

        $userId = $user->user_id; // This is the string user_id (e.g., "67686")

        // Prevent self-archiving
        if ($userId == $validated['other_user_id']) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid operation.'], 400);
            }
            return back()->with('error', 'Invalid operation.');
        }

        // Create or update archive record
        $archived = ArchivedMessage::updateOrCreate(
            [
                'user_id' => $userId,
                'advert_id' => $validated['advert_id'],
                'other_user_id' => $validated['other_user_id'],
            ],
            [
                'archived_at' => now(),
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Conversation archived successfully. It will be hidden from your message list.',
                'archived' => $archived
            ], 200);
        }

        return redirect("user/messages")->with('success', 'Conversation archived successfully.');

    }


    public function unarchive(Request $request)
    {
        $validated = $request->validate([
            'advert_id' => 'required|exists:adverts,id',
            'other_user_id' => 'required|exists:users,user_id',
        ]);

        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect('/login');
        }

        $userId = $user->user_id;

        $deleted = ArchivedMessage::where('user_id', $userId)
            ->where('advert_id', $validated['advert_id'])
            ->where('other_user_id', $validated['other_user_id'])
            ->delete();

        if ($deleted) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Conversation unarchived successfully.'], 200);
            }
            return redirect("user/messages")->with('success', 'Conversation unarchived successfully.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Archive not found.'], 404);
        }
        return back()->with('error', 'Archive not found.');
    }
}
