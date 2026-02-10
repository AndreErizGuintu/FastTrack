<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourierController extends Controller
{
    /**
     * Show courier dashboard with available orders
     */
    public function dashboard()
    {
        // Get courier's active order (if any)
        $activeOrder = DeliveryOrder::forCourier(auth()->id())
            ->active()
            ->with('user:id,name')
            ->first();

        // Get available pending orders (if courier has no active order)
        $availableOrders = $activeOrder ? collect() : DeliveryOrder::pending()
            ->with('user:id,name')
            ->latest()
            ->get();

        // Get courier history (delivered or cancelled)
        $orderHistory = DeliveryOrder::forCourier(auth()->id())
            ->whereIn('status', ['delivered', 'cancelled'])
            ->with('user:id,name')
            ->latest()
            ->take(10)
            ->get();

        return view('courier.dashboard', compact('activeOrder', 'availableOrders', 'orderHistory'));
    }

    /**
     * Accept a delivery order
     */
    public function acceptOrder(DeliveryOrder $order)
    {
        // Check if courier already has an active order
        $activeOrder = DeliveryOrder::forCourier(auth()->id())
            ->active()
            ->exists();

        if ($activeOrder) {
            return back()->with('error', 'You already have an active delivery. Complete it before accepting another.');
        }

        // Check if order is still pending
        if ($order->status !== 'pending') {
            return back()->with('error', 'This order has already been accepted by another courier.');
        }

        // Use database transaction to prevent race conditions
        DB::transaction(function () use ($order) {
            $order->acceptByCourier(auth()->id());
        });

        return redirect()->route('courier.dashboard')
            ->with('success', 'Order accepted! You can now start the delivery.');
    }

    /**
     * Mark order as picked up (in transit)
     */
    public function pickupOrder(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier can pickup
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        if ($order->status !== 'accepted') {
            return back()->with('error', 'Order must be in accepted status to mark as picked up.');
        }

        $order->markPickedUp();

        return back()->with('success', 'Order marked as picked up. Now in transit!');
    }

    /**
     * Mark order as delivered
     */
    public function deliverOrder(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier can deliver
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        if ($order->status !== 'in_transit') {
            return back()->with('error', 'Order must be in transit to mark as delivered.');
        }

        $order->markDelivered();

        return redirect()->route('courier.dashboard')
            ->with('success', 'Order delivered successfully! Great job!');
    }

    /**
     * Cancel order (courier cancellation)
     */
    public function cancelOrder(Request $request, DeliveryOrder $order)
    {
        // Authorization: Only assigned courier can cancel their accepted order
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        if ($order->isCompleted()) {
            return back()->with('error', 'Cannot cancel a completed order.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $order->cancel($validated['cancellation_reason']);

        return redirect()->route('courier.dashboard')
            ->with('success', 'Order cancelled.');
    }
}
