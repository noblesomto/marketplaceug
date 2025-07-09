<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\User;
use App\Models\Advert;
use App\Models\Payment;
use Carbon\Carbon;

class MessageController extends Controller
{
    public function sendMessage22(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        $message = Message::create([
            'advert_id' => $request->ad_id,
            'receiver_id' => $request->ad_owner,
            'sender_id' => $request->session()->get('user_id'),
            'message_content' => $request->message,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json(['status' => 'Message Sent!']);
    }

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

    public function Message(){
                $ad_owner = DB::table('messages')
                ->join('users','messages.user_id', '=', 'users.user_id')
                ->where('messages.ad_id',$ad_id)
                ->where('messages.user_id',$user_id)
                ->first();
        if($ad_owner){     
            $userId = $ad_owner->ad_owner;
            //dd($userId);
            $message['user'] = User::where('user_id', $userId)->first();
        }
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
        
        $messages = Message::where(function ($query) use ($sender, $receiver, $advertId) {
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

        return view('dashboard.chat', compact('title','messages', 'advert', 'receiver','user','count_ads','payment'));
    }

    public function sendMessage(Request $request, $advertId, $receiverId)
    {
        $request->validate(['message' => 'required|string']);

        $message = new Message();
        $message->advert_id = $advertId;
        $message->sender_id = $request->session()->get('user_id');
        $message->receiver_id = $receiverId;
        $message->message_content = $request->message;
        $message->is_read = false;
        $message->save();

        event(new NewMessageNotification($message));
        return redirect()->back();
    }

    public function countUnreadMessages()
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
