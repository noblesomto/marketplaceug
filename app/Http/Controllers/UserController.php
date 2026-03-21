<?php

namespace App\Http\Controllers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Models\ArchivedMessage;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\State;
use App\Models\Feedback;
use App\Models\Payment;
use App\Models\Wishlist;
use App\Models\Category;
use App\Models\Followers;
use App\Models\Notification;
use App\Models\ReportUser;
use Carbon\Carbon;
use App\Rules\ReCaptcha;
use Mail;
use App\Mail\ReportMail;
use App\Traits\HasUserSession;

class UserController extends Controller
{
    use HasUserSession;
        public function index(Request $request)
    {
        $title = "User Dashboard - " . config('global.site_name');
        $user = $this->getUserFromSession();
        $user_id = $user->user_id;
        $count_ads = Advert::where('user_id', $user_id)->count();
        $ads = Advert::with('firstImage')
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->paginate(20);

        $hasMore = $ads->hasMorePages();

        return view('dashboard.index', compact('title', 'user', 'ads', 'count_ads', 'hasMore'));
    }

    public function my_ads(Request $request)
    {
        $title = "My Ads | " . config('global.site_name');
        $user = $this->getUserFromSession();
        $user_id = $user->user_id;
        $ads = Advert::with('firstImage')
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->paginate(20);
        $count_ads = Advert::where('user_id', $user_id)->count();

        $hasMore = $ads->hasMorePages();

        return view('dashboard.my-ads', compact('title', 'user', 'ads', 'count_ads', 'hasMore'));
    }

    public function loadMoreUserAds(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        if (!$user_id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $ads = Advert::with('firstImage')
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->paginate(20);

        return response()->json([
            'html' => view('dashboard.components.my-ads', ['ads' => $ads])->render(),
            'hasMore' => $ads->hasMorePages()
        ]);
    }

    public function category(Request $request)
    {   
        $title = "User Dashboard  - " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $categories = Category::orderBy('category','asc')->get();
        
        return view('dashboard.category', compact('title','user','categories'));
    }


    public function messages(Request $request)
    {
        $title = "User Dashboard  - " . config('global.site_name');
        $userId = $request->session()->get('user_id');
        $user = User::where('users.user_id', $userId)->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Please login to view messages');
        }

        $count_ads = Advert::where('user_id', $userId)->count();

        // Get archived conversation keys for this user (using user_id string)
        $archivedConversations = ArchivedMessage::where('user_id', $userId)
            ->get()
            ->map(function($archive) {
                return $archive->advert_id . '_' . $archive->other_user_id;
            })
            ->toArray();

        // Fetch distinct conversations for the logged-in user as either sender or receiver
        $allConversations = Message::select(
                DB::raw("(CASE WHEN sender_id = '{$userId}' THEN receiver_id ELSE sender_id END) AS other_user_id"),
                'advert_id',
                DB::raw('MAX(created_at) as last_message_at'),
                DB::raw('MAX(id) as last_message_id')
            )
            ->where(function($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->groupBy('other_user_id', 'advert_id')
            ->get();

        // Filter out archived conversations
        $filteredConversations = $allConversations->filter(function($conversation) use ($archivedConversations) {
            $key = $conversation->advert_id . '_' . $conversation->other_user_id;
            return !in_array($key, $archivedConversations);
        });

        // Sort by last message date and reset keys
        $conversations = $filteredConversations->sortByDesc('last_message_at')->values();

        // Load additional details
        $conversations = $conversations->map(function ($conversation) use ($userId) {
            // Fetch advert and other user details
            $advert = Advert::find($conversation->advert_id);
            $otherUserId = $conversation->other_user_id;
            $otherUser = User::where('user_id', $otherUserId)->first();

            // Get the last message in this conversation
            $lastMessage = Message::where('advert_id', $conversation->advert_id)
                ->where(function($query) use ($userId, $otherUserId) {
                    $query->where(function($q) use ($userId, $otherUserId) {
                        $q->where('sender_id', $userId)
                          ->where('receiver_id', $otherUserId);
                    })->orWhere(function($q) use ($userId, $otherUserId) {
                        $q->where('sender_id', $otherUserId)
                          ->where('receiver_id', $userId);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->first();

            // Count unread messages in this conversation
            $unreadCount = Message::where('advert_id', $conversation->advert_id)
                ->where('receiver_id', $userId)
                ->where('sender_id', $otherUserId)
                ->where('is_read', false)
                ->count();

            return [
                'advert' => $advert,
                'other_user' => $otherUser,
                'unread_count' => $unreadCount,
                'last_message' => $lastMessage,
                'last_message_at' => $conversation->last_message_at
            ];
        });

        return view('dashboard.messages', compact('title', 'user', 'conversations', 'count_ads'));
    }

    public function archivedMessages(Request $request)
    {
        $title = "Archived Messages - " . config('global.site_name');
        $userId = $request->session()->get('user_id');
        $user = User::where('users.user_id', $userId)->first();

        if (!$user) {
            return redirect('/login')->with('error', 'Please login to view messages');
        }

        $count_ads = Advert::where('user_id', $userId)->count();

        // Get archived conversations
        $archivedConversations = ArchivedMessage::where('user_id', $userId)
            ->with(['advert', 'otherUser'])
            ->orderBy('archived_at', 'desc')
            ->get()
            ->map(function($archive) use ($userId) {
                // Get the last message in this conversation
                $lastMessage = Message::where('advert_id', $archive->advert_id)
                    ->where(function($query) use ($userId, $archive) {
                        $query->where(function($q) use ($userId, $archive) {
                            $q->where('sender_id', $userId)
                              ->where('receiver_id', $archive->other_user_id);
                        })->orWhere(function($q) use ($userId, $archive) {
                            $q->where('sender_id', $archive->other_user_id)
                              ->where('receiver_id', $userId);
                        });
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                // Count unread messages in this conversation
                $unreadCount = Message::where('advert_id', $archive->advert_id)
                    ->where('receiver_id', $userId)
                    ->where('sender_id', $archive->other_user_id)
                    ->where('is_read', false)
                    ->count();

                return [
                    'advert' => $archive->advert,
                    'other_user' => $archive->otherUser,
                    'unread_count' => $unreadCount,
                    'last_message' => $lastMessage,
                    'archived_at' => $archive->archived_at,
                    'archive_id' => $archive->id,
                    'advert_id' => $archive->advert_id,
                    'other_user_id' => $archive->other_user_id
                ];
            });

        return view('dashboard.archived-messages', compact('title', 'user', 'archivedConversations', 'count_ads'));
    }





    public function ad_status($status , $id)
    {   
        DB::table('adverts')
            ->where('id', $id)
            ->update([
                'ad_status'=> $status,
            ]);
 
        return redirect("user/my-ads")->with('success', 'Advert Status Updated');
    
    }

    public function add_wishlist(Request $request, $id)
    {   
        $user_id = $request->session()->get('user_id');

        if (!$user_id) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Please login'], 401);
            }
            return redirect()->back()->with('error', 'Please login to add to wishlist');
        }

        $advert = Advert::where('id', $id)->first();

        if (!$advert) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Advert not found'], 404);
            }
            return redirect()->back()->with('error', 'Advert not found');
        }

        $wishlist = Wishlist::where('advert_id', $id)
                            ->where('user_id', $user_id)
                            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $message = 'Removed from Wishlist';
            $inWishlist = false;
        } else {
            $newpost = new Wishlist(['user_id' => $user_id]);
            $newpost->advert()->associate($advert);
            $newpost->save();
            $message = 'Added to Wishlist';
            $inWishlist = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'in_wishlist' => $inWishlist
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function favourites(Request $request)
    {
        $title = "Favourite Adverts | " . config('global.site_name');
        
        $userId = $request->session()->get('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            abort(404, 'User not found');
        }
        
        $favoriteAds = Advert::with('firstImage')
            ->whereHas('wishlists', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $countAds = Advert::where('user_id', $userId)->count();
        
        return view('dashboard.favourites', compact(
            'title',
            'user',
            'favoriteAds',
            'countAds'
        ));
    }

  public function payments(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        $buyAds = Payment::with(['advert.media', 'shipping'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.payments', [
            'title' => "Buy Direct Adverts | " . config('global.site_name'),
            'user' => $user,
            'buyAds' => $buyAds,
        ]);
    }

    public function confirmDelivery(Request $request, $orderId)
    {
        try {
            // Find the order
            $order = Payment::findOrFail($orderId);

            // Optional: Add authorization check
            // $this->authorize('update', $order);

            // Update the order status
            $order->buyer_status = 'delivered';
            $order->shipping_status_date = now(); // Optional: Add timestamp
            $order->save();

            // Log the action (optional)
            \Log::info("Order {$orderId} marked as delivered by user " . auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Delivery confirmed successfully',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            \Log::error("Error confirming delivery for order {$orderId}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm delivery. Please try again.'
            ], 500);
        }
    }

    public function ad_shipping(Request $request, $id)
    {
        $userId = $request->session()->get('user_id');
        $user = User::findOrFail($userId);

        // Eager load advert and its firstImage
        $ad = Payment::with(['advert.firstImage','shipping'])
            ->where('advert_id', $id)
            ->first();
        //dd($ad);
        return view('dashboard.ad-shipping', [
            'title' => "Ad Shipping | " . config('global.site_name'),
            'user' => $user,
            'ad' => $ad,
        ]);
    }

    public function update_shipping(Request $request, $id)
    {   
        $request->validate([
            'shipping_status' => 'required'
        ]);

        DB::table('payments')
            ->where('id', $id)
            ->update([
                'shipping_status'=> $request->input('shipping_status'),
                'shipping_status_date'=> now(),
            ]);
 
        return redirect()->back()->with('success', 'Shipping Status Updated');
    
    }

    public function advert_sold($id)
    {   
        DB::table('adverts')
            ->where('id', $id)
            ->update([
                'sold'=> "Yes",
                'sold_date'=> Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
 
        return redirect()->back()->with('success', 'Advert Marked Sold');
    
    }

    public function reviews_seller(Request $request, $id)
    {
        $title = "Feedbacks | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $seller = User::where('user_id', $id)->first();
        $feedbacks = Feedback::with('user')->orderBy('created_at', 'asc')->where('seller_id', $id)->paginate(20);
        $count_feedbacks = Feedback::where('seller_id', $id)->count();
        $count_ads = Advert::where('user_id', $user_id)->count();
        return view('dashboard.reviews-seller', compact('title','user', 'seller', 'feedbacks','count_ads','count_feedbacks'));
    }

    public function feedbacks(Request $request)
    {
        $title = "Feedbacks | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $feedbacks = Feedback::with('user')->orderBy('created_at', 'asc')->where('seller_id', $user_id)->paginate(20);
        $count_feedbacks = Feedback::where('seller_id', $user_id)->count();
        $count_ads = Advert::where('user_id', $user_id)->count();
        return view('dashboard.feedbacks', compact('title','user', 'feedbacks','count_ads','count_feedbacks'));
    }

    public function submit_feedback(Request $request, $seller)
    {
        $title = "Feedbacks | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();
        $seller = User::where('user_id', $seller)->first();
        $feedback = Feedback::with('user')->where('user_id', $user_id)->where('seller_id', $seller->user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        //dd($feedback);
        if ($request->isMethod('GET')) {
        return view('dashboard.submit-feedbacks', compact('title','user', 'feedback','count_ads','seller'));
        }

        if ($request->isMethod('POST')) {

            $validated = $request->validate([
                'rating' => 'required|integer|between:1,5',
                'satisfaction' => 'required|integer|between:1,5',
                'reliable' => 'required|integer|between:1,5',
                'friendly' => 'required|integer|between:1,5',
                'message' => 'required|string|max:1000'
            ]);

            $validated['user_id'] = $user_id;
            $validated['seller_id'] = $seller->user_id;

            Feedback::updateOrCreate(
                [
                    'user_id' => $user_id,
                    'seller_id' => $seller->user_id
                ],
                $validated
            );

            return redirect()->back()->with('success', 'Thank you for your review!');
        }
    }


    public function boost_ad(Request $request, $id)
    {   
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();

        return view('dashboard.boost-ad', compact('title','user','advert','count_ads', 'price'));
    }

    public function post_boost_ad(Request $request, $id)
    {
        $title = "Boost Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $promotion = $request->session()->get('promotion');
        $count_ads = Advert::where('user_id', $user_id)->count();
        $advert = Advert::with(['images', 'car', 'phone'])
                        ->where('id', $id)
                        ->where('user_id', $user_id)
                        ->firstOrFail();

        return view('dashboard.post-boost-ad', compact('title','user','advert','count_ads','promotion'));
    }

    public function boosted_ad(Request $request, $id)
    {   
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $advert = AdvertBoost::with(['images', 'car', 'phone', 'boost'])
                ->where('id', $id)
                ->where('user_id', $user_id)
                ->firstOrFail();

        return view('dashboard.boosted-ad', compact('title','user','advert','count_ads', 'price'));
    }

    public function boosted_ads(Request $request)
    {
        $title = "Boosted Ad | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();
        $count_ads = Advert::where('user_id', $user_id)->count();
        $price = 500;
        $ads = AdvertBoost::with(['user', 'advert.media'])
                ->where('user_id', $user_id)
                ->orderby('updated_at','desc')
                ->paginate(10);
        return view('dashboard.boosted-adverts', compact('title','user','ads','count_ads', 'price'));
    }




    public function checkFollowing(Request $request, $userId)
    {
        $user_id = $request->session()->get('user_id');
        $user = User::where('user_id', $user_id)->first();

        $isFollowing = Followers::where([
            'user_id' => $user_id,
            'follow' => $userId
        ])->exists();

        return response()->json(['isFollowing' => $isFollowing]);
    }

    public function toggleFollow(Request $request)
    {
        $user_id = $request->session()->get('user_id');

        $request->validate([
            'followee_id' => 'required|exists:users,user_id'
        ]);

        $follow = Followers::where([
            'user_id' => $user_id,
            'follow' => $request->followee_id
        ])->first();

        if ($follow) {
            $follow->delete();
            return response()->json(['isFollowing' => false, 'message' => 'Unfollowed successfully']);
        } else {
            Followers::create([
                'user_id' => $user_id,
                'follow' => $request->followee_id
            ]);
            return response()->json(['isFollowing' => true, 'message' => 'Followed successfully']);
        }
    }

    public function notifications(Request $request) {
        $title = "My Notifications | " . config('global.site_name');
        $user_id = $request->session()->get('user_id');
        $user = User::where('users.user_id', $user_id)->first();

        // Stamp the time the user opened this page — resets the badge counter
        // without touching individual is_read states on each notification.
        $user->notifications_seen_at = now();
        $user->save();

        $notifications = Notification::with([
                'advert.owner',
                'advert.media' // Add this to eager load Spatie media
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $count_ads = Advert::where('user_id', $user_id)->count();

        $groupedNotifications = $notifications->groupBy(function ($notification) {
            $date = $notification->created_at;
            if ($date->isToday()) {
                return 'Today';
            } elseif ($date->isYesterday()) {
                return 'Yesterday';
            } elseif ($date->isCurrentWeek()) {
                return $date->format('l');
            } else {
                return $date->format('M j, Y');
            }
        });

        return view('dashboard.notifications', compact('title','user', 'groupedNotifications','count_ads'));
    }

    public function deleteNotification($id)
    {
        $notification = Notification::where('id', $id)->first();

        if (! $notification) {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $notification->delete();

        return response()->json(['status' => 'success']);
    }


    public function report_user(Request $request, $id)
{
    $data['title'] = 'Report User | '.config('global.site_name');
    $data['reported']  = $reported = User::where('user_id', $id)->first();
    $user_id = $request->session()->get('user_id');
    $data['reporter'] = $user = User::where('user_id', $user_id)->first();

    if ($request->isMethod('GET')) {
        return view('frontend.report-user', $data);
    }

    if ($request->isMethod('POST')) {
        // Build validation rules
        $rules = [
            'name' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ];

        // Add reCAPTCHA validation only if enabled
        if (config('services.recaptcha.enabled', false)) {
            $rules['g-recaptcha-response'] = ['required', new ReCaptcha];
        }

        $request->validate($rules);

        $message = ReportUser::updateOrCreate(
            [
                'reported' => $reported->id,
                'reporter' => $user->id,
            ],
            [
                'subject' => $request->subject,
                'message' => $request->message,
            ]
        );

        $details = [
            'advert' => $reported->name,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        // Send email in background
        Mail::to(config('global.admin_email'))
            ->queue(new ReportMail($details));

        return redirect()->back()
            ->with('success', 'Your Report Has Been Received, We will Get back to Shortly');
    }
}



}
