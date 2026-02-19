<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderStatusHistory;

class NotificationController extends Controller
{
    /**
     * Mark a single notification as seen
     */
    public function markSeen($notificationId)
    {
        $notification = OrderStatusHistory::findOrFail($notificationId);
        
        // Ensure the notification belongs to the current user's order
        if ($notification->deliveryOrder->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $notification->update(['seen_at' => now()]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Mark all notifications as seen for the current user
     */
    public function markAllSeen()
    {
        OrderStatusHistory::whereNull('seen_at')
            ->whereIn('new_status', ['accepted', 'picked_up', 'arriving_at_dropoff', 'cancelled_by_user', 'cancelled_by_courier', 'cancelled_by_system'])
            ->whereHas('deliveryOrder', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->update(['seen_at' => now()]);
        
        return response()->json(['success' => true]);
    }
}
