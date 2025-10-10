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


class MessageController extends Controller
{


    public function fetchMessages(Request $request, $id, $owner)
    {   
        $user_id = $request->session()->get('user_id');
        $message = DB::table('messages')
            ->join('users', 'messages.sender_id', '=', 'users.user_id')
            ->where('messages.advert_id', $id)
            ->where(function ($query) use ($user_id, $owner) {
                $query->where('messages.sender_id', $user_id)
                      ->orWhere('messages.receiver_id', $owner);
            })
            ->get();

        //dd($message);
        return ($message);
    }

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
            'images.*'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            return redirect()->back()->with('error', 'You cannot send messages to this user.');
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
                $path = FileUploadHelper::upload($imageFile, 'chat');

                MessageImage::create([
                    'message_id' => $message->id,
                    'image_path' => $path,
                ]);
            }
        }

        // Fire event
        event(new NewMessageNotification($message));

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
}
