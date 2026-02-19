<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\OrderStatusHistory;
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
        return redirect()->route('user.dashboard')
            ->with('info', 'Create a new order using the "New Order" button in your dashboard.');
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
            'status' => 'draft', // Start in draft, then transition to awaiting_courier
            ...$validated,
        ]);

        // Log the creation
        OrderStatusHistory::create([
            'delivery_order_id' => $order->id,
            'old_status' => null,
            'new_status' => 'draft',
            'changed_by' => auth()->id(),
            'actor_type' => 'user',
            'reason' => 'Order created',
        ]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Delivery order created! Review and confirm to make it available to couriers.')
            ->with('recent_order_id', $order->id);
    }

    /**
     * Display specific order details
     */
    public function show(DeliveryOrder $order)
    {
        // Authorization: User can only view their own orders, courier can view assigned orders
        if ($order->user_id !== auth()->id() && $order->courier_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load([
            'courier:id,name',
            'user:id,name',
            'statusHistory.changedBy:id,name',
            'courierLocations',
        ]);

        return view('user.orders.show', compact('order'));
    }

    /**
     * Confirm order (transition from draft → awaiting_courier)
     */
    public function confirm(DeliveryOrder $order)
    {
        // Authorization: Only order creator can confirm
        if ($order->user_id !== auth()->id()) {
            abort(403, 'You can only confirm your own orders.');
        }

        // Validation: Order must be in draft status
        if ($order->status !== 'draft') {
            return back()->with('error', 'Order can only be confirmed from draft status. Current status: ' . $order->status);
        }

        DB::transaction(function () use ($order) {
            try {
                $order->transitionTo('awaiting_courier');
                
                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => 'draft',
                    'new_status' => 'awaiting_courier',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'user',
                    'reason' => 'Order confirmed and posted for couriers',
                ]);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception('Invalid status transition: ' . $e->getMessage());
            }
        });

        return back()
            ->with('success', 'Order confirmed! Couriers can now see and accept your order.');
    }

    /**
     * Update draft order
     */
    public function update(Request $request, DeliveryOrder $order)
    {
        // Authorization: Only order creator can update
        if ($order->user_id !== auth()->id()) {
            abort(403, 'You can only update your own orders.');
        }

        // Validation: Order must be in draft status
        if ($order->status !== 'draft') {
            return back()->with('error', 'Only draft orders can be updated.');
        }

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

        $order->update($validated);

        return back()->with('success', 'Order updated successfully.');
    }

    /**
     * Cancel order by user
     * 
     * RULES:
     * - Can only cancel if status is: draft, awaiting_courier, or accepted
     * - NOT allowed after: picked_up, in_transit, delivered
     */
    public function cancel(Request $request, DeliveryOrder $order)
    {
        // Authorization: Only order creator can cancel
        if ($order->user_id !== auth()->id()) {
            abort(403, 'You can only cancel your own orders.');
        }

        // Validation: Check if order is cancellable
        if (!$order->isCancellableByUser()) {
            return back()->with('error', sprintf(
                'Sorry, you cannot cancel this order anymore. The courier has already collected your item and it\'s on the way to delivery. ' .
                'Please contact support if you need assistance. Current status: %s',
                str_replace('_', ' ', ucfirst($order->status))
            ))->withInput();
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($order, $validated) {
            try {
                $oldStatus = $order->status;
                $order->cancelByUser($validated['cancellation_reason']);

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled_by_user',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'user',
                    'reason' => $validated['cancellation_reason'],
                ]);

                // If courier was assigned, clear the assignment
                if ($order->courier_id) {
                    $order->update(['courier_id' => null]);
                }
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return redirect()->route('user.orders.index')
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * Reject order (courier rejected before accepting)
     * Used by courier to pass on order (from awaiting_courier state)
     */
    public function reject(Request $request, DeliveryOrder $order)
    {
        // Authorization: Order must not be currently assigned to this courier
        if ($order->courier_id === auth()->id()) {
            abort(403, 'You cannot reject an order you have already accepted.');
        }

        // Order must be awaiting courier
        if ($order->status !== 'awaiting_courier') {
            return back()->with('error', 'Order must be in "awaiting_courier" status to reject.');
        }

        // Note: We don't transition the order, just log the rejection
        // (This allows other couriers to still see and accept it)
        
        OrderStatusHistory::create([
            'delivery_order_id' => $order->id,
            'old_status' => 'awaiting_courier',
            'new_status' => 'awaiting_courier', // No transition, just a log
            'changed_by' => auth()->id(),
            'actor_type' => 'courier',
            'reason' => 'Courier rejected (order still available)',
        ]);

        return back()->with('info', 'You have passed on this order. Other couriers can still accept it.');
    }

    /**
     * Reorder - Create a draft copy of a delivered order
     */
    public function reorder(DeliveryOrder $order)
    {
        // Authorization: Only order creator can reorder
        if ($order->user_id !== auth()->id()) {
            abort(403, 'You can only reorder your own orders.');
        }

        // Validation: Order must be in delivered status
        if ($order->status !== 'delivered') {
            return back()->with('error', 'Only delivered orders can be reordered.');
        }

        // Create new draft order with same details
        $newOrder = DeliveryOrder::create([
            'user_id' => auth()->id(),
            'status' => 'draft',
            'product_description' => $order->product_description,
            'estimated_weight' => $order->estimated_weight,
            'special_notes' => $order->special_notes,
            'pickup_address' => $order->pickup_address,
            'pickup_contact_phone' => $order->pickup_contact_phone,
            'delivery_address' => $order->delivery_address,
            'delivery_contact_phone' => $order->delivery_contact_phone,
            'delivery_fee' => $order->delivery_fee, // Use previous fee as estimate
        ]);

        // Log the creation from reorder
        OrderStatusHistory::create([
            'delivery_order_id' => $newOrder->id,
            'old_status' => null,
            'new_status' => 'draft',
            'changed_by' => auth()->id(),
            'actor_type' => 'user',
            'reason' => "Reordered from order #{$order->id}",
        ]);

        return redirect()->route('user.orders.show', $newOrder)
            ->with('success', 'Order created as draft! Review the details and confirm to send it to couriers.');
    }
}
