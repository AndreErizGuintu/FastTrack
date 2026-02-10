<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    /**
     * Display chat for a specific order
     */
    public function index(DeliveryOrder $order)
    {
        // Authorization: Only user and assigned courier can view chat
        if ($order->user_id !== auth()->id() && $order->courier_id !== auth()->id()) {
            abort(403, 'You do not have access to this chat.');
        }

        $order->load(['user:id,name,role', 'courier:id,name,role']);
        
        $messages = Message::forOrder($order->id)
            ->with('sender:id,name,role')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read (messages sent by the other party)
        Message::forOrder($order->id)
            ->where('sender_id', '!=', auth()->id())
            ->unread()
            ->each(function ($message) {
                $message->markAsRead();
            });

        return view('user.orders.chat', compact('order', 'messages'));
    }

    /**
     * Send a message in order chat
     */
    public function store(Request $request, DeliveryOrder $order)
    {
        // Authorization: Only user and assigned courier can send messages
        if ($order->user_id !== auth()->id() && $order->courier_id !== auth()->id()) {
            abort(403, 'You do not have access to this chat.');
        }

        // Check if chat is active
        if (!$order->isChatActive()) {
            return back()->with('error', 'Chat is closed for this order.');
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'message_type' => 'nullable|in:text,image,location',
        ]);

        Message::create([
            'delivery_order_id' => $order->id,
            'sender_id' => auth()->id(),
            'message' => $validated['message'],
            'message_type' => $validated['message_type'] ?? 'text',
        ]);

        return back()->with('success', 'Message sent!');
    }

    /**
     * Get unread message count for an order
     */
    public function unreadCount(DeliveryOrder $order)
    {
        // Authorization check
        if ($order->user_id !== auth()->id() && $order->courier_id !== auth()->id()) {
            abort(403);
        }

        $count = Message::forOrder($order->id)
            ->where('sender_id', '!=', auth()->id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }
}
