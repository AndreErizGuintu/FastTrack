<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryOrderController extends Controller
{
    /**
     * Display user's delivery orders
     */
    public function index()
    {
        $orders = DeliveryOrder::forUser(auth()->id())
            ->with('courier:id,name')
            ->latest()
            ->paginate(10);

        return view('user.orders.index', compact('orders'));
    }

    /**
     * Show form to create new delivery order
     */
    public function create()
    {
        return view('user.orders.create');
    }

    /**
     * Store new delivery order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_description' => 'required|string|max:1000',
            'estimated_weight' => 'required|numeric|min:0.1',
            'special_notes' => 'nullable|string|max:1000',
            'pickup_address' => 'required|string|max:500',
            'pickup_contact_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:500',
            'delivery_contact_phone' => 'required|string|max:20',
            'delivery_fee' => 'nullable|numeric|min:0',
        ]);

        $order = DeliveryOrder::create([
            'user_id' => auth()->id(),
            'status' => 'pending',
            ...$validated,
        ]);

        return redirect()->route('user.orders.index')
            ->with('success', 'Delivery order created successfully! Couriers can now see your order.');
    }

    /**
     * Display specific order details
     */
    public function show(DeliveryOrder $order)
    {
        // Authorization: User can only view their own orders
        if ($order->user_id !== auth()->id() && $order->courier_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['courier:id,name', 'user:id,name', 'statusHistory.changedBy:id,name']);

        return view('user.orders.show', compact('order'));
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, DeliveryOrder $order)
    {
        // Authorization: Only order creator can cancel
        if ($order->user_id !== auth()->id()) {
            abort(403, 'You can only cancel your own orders.');
        }

        // Cannot cancel if already delivered or cancelled
        if ($order->isCompleted()) {
            return back()->with('error', 'Cannot cancel an order that is already completed.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $order->cancel($validated['cancellation_reason']);

        return redirect()->route('user.orders.index')
            ->with('success', 'Order cancelled successfully.');
    }
}
