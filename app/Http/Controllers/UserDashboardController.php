<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\OrderStatusHistory;

class UserDashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function dashboard()
    {
        $unreadNotifications = OrderStatusHistory::whereNull('seen_at')
            ->whereIn('new_status', ['accepted', 'picked_up', 'arriving_at_dropoff', 'delivered', 'cancelled_by_user', 'cancelled_by_courier', 'cancelled_by_system'])
            ->whereHas('deliveryOrder', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['deliveryOrder', 'changedBy'])
            ->latest()
            ->get()
            ->unique(function ($item) {
                return $item->delivery_order_id . '-' . $item->old_status . '-' . $item->new_status;
            });

        $userOrders = DeliveryOrder::forUser(auth()->id())
            ->with('courier:id,name')
            ->latest()
            ->take(10)
            ->get();

        return view('user.dashboard', [
            'unreadNotifications' => $unreadNotifications,
            'unreadCount' => $unreadNotifications->count(),
            'userOrders' => $userOrders,
        ]);
    }
}
