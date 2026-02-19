<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\OrderStatusHistory;
use App\Services\CourierLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourierController extends Controller
{
    /**
     * Show courier dashboard with available orders
     * 
     * DISPLAY LOGIC:
     * - Active orders: Any order assigned to courier that is NOT in terminal state
     * - Available orders: Orders in 'awaiting_courier' status with no courier assigned
     * - History: Orders in terminal states (delivered, cancelled_*, expired)
     */
    public function dashboard()
    {
        // Get courier's active order (if any) - strictly assigned and not terminal
        $activeOrder = DeliveryOrder::forCourier(auth()->id())
            ->active()
            ->with(['user:id,name', 'courierLocations'])
            ->first();

        // Get available pending orders (if courier has no active order)
        // Only show orders in 'awaiting_courier' status with no courier assigned
        $availableOrders = $activeOrder ? collect() : DeliveryOrder::where('status', 'awaiting_courier')
            ->whereNull('courier_id')
            ->with('user:id,name')
            ->latest()
            ->get();

        // Get courier history (terminal states)
        $orderHistory = DeliveryOrder::forCourier(auth()->id())
            ->whereIn('status', [
                'delivered',
                'cancelled_by_user',
                'cancelled_by_courier',
                'cancelled_by_system',
                'expired',
                'returned',
            ])
            ->with('user:id,name')
            ->latest()
            ->take(10)
            ->get();

        return view('courier.dashboard', compact('activeOrder', 'availableOrders', 'orderHistory'));
    }

    /**
     * Accept a delivery order
     * 
     * RESTRICTIONS:
     * - Courier must NOT have an active order
     * - Order must be in 'awaiting_courier' status
     * - Order must NOT have a courier assigned
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

        // STRICT: Order must be in 'awaiting_courier' status
        if ($order->status !== 'awaiting_courier') {
            return back()->with('error', 'This order is no longer available. Status: ' . str_replace('_', ' ', ucfirst($order->status)));
        }

        // STRICT: Order must NOT have a courier already assigned
        if ($order->courier_id !== null) {
            return back()->with('error', 'This order has already been accepted by another courier.');
        }

        // Use database transaction to prevent race conditions
        DB::transaction(function () use ($order) {
            try {
                $order->update([
                    'courier_id' => auth()->id(),
                ]);
                
                $order->transitionTo('accepted');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => 'awaiting_courier',
                    'new_status' => 'accepted',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'Courier accepted order',
                ]);

                // Record current location
                CourierLocationService::recordLocationForStatusUpdate(
                    $order,
                    'accepted',
                    address: 'Accepted order location'
                );
            } catch (\InvalidArgumentException $e) {
                throw new \Exception('Cannot accept order: ' . $e->getMessage());
            }
        });

        return redirect()->route('courier.dashboard')
            ->with('success', 'Order accepted! Review details and head to pickup location.');
    }

    /**
     * Transition to arriving at pickup
     */
    public function arrivingAtPickup(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // Must be in 'accepted' state
        if ($order->status !== 'accepted') {
            return back()->with('error', 'Order must be in accepted status to start moving to pickup.');
        }

        DB::transaction(function () use ($order) {
            try {
                $order->transitionTo('arriving_at_pickup');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => 'accepted',
                    'new_status' => 'arriving_at_pickup',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'Courier heading to pickup location',
                ]);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return back()->with('success', 'On your way to pickup location!');
    }

    /**
     * Transition to at pickup location
     */
    public function atPickup(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // Must be arriving or already accepted
        if (!in_array($order->status, ['accepted', 'arriving_at_pickup'])) {
            return back()->with('error', 'Order must be in accepted or arriving_at_pickup status.');
        }

        DB::transaction(function () use ($order) {
            try {
                $currentStatus = $order->status;
                
                // If coming from 'accepted', transition through 'arriving_at_pickup' first
                if ($currentStatus === 'accepted') {
                    $order->transitionTo('arriving_at_pickup');
                }
                
                $order->transitionTo('at_pickup');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => $currentStatus,
                    'new_status' => 'at_pickup',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'Courier arrived at pickup location',
                ]);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return back()->with('success', 'You are at the pickup location. Collect the item and confirm pickup.');
    }

    /**
     * Mark order as picked up
     */
    public function pickupOrder(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier can pickup
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // Must be at pickup location
        if ($order->status !== 'at_pickup') {
            return back()->with('error', 'You must be at the pickup location first. Current status: ' . str_replace('_', ' ', $order->status));
        }

        DB::transaction(function () use ($order) {
            try {
                $order->transitionTo('picked_up');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => 'at_pickup',
                    'new_status' => 'picked_up',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'Item collected',
                ]);

                // Record pickup location
                CourierLocationService::recordLocationForStatusUpdate(
                    $order,
                    'picked_up',
                    address: $order->pickup_address
                );
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return back()->with('success', 'Item picked up! Starting transit to delivery location.');
    }

    /**
     * Transition to in transit
     */
    public function inTransit(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // Must be picked up
        if ($order->status !== 'picked_up') {
            return back()->with('error', 'Order must be picked up before transit.');
        }

        DB::transaction(function () use ($order) {
            try {
                $order->transitionTo('in_transit');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => 'picked_up',
                    'new_status' => 'in_transit',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'In transit to delivery location',
                ]);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return back()->with('success', 'Package in transit!');
    }

    /**
     * Transition to arriving at dropoff
     */
    public function arrivingAtDropoff(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // Must be in transit
        if ($order->status !== 'in_transit') {
            return back()->with('error', 'Order must be in transit first.');
        }

        DB::transaction(function () use ($order) {
            try {
                $order->transitionTo('arriving_at_dropoff');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => 'in_transit',
                    'new_status' => 'arriving_at_dropoff',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'Approaching delivery location',
                ]);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return back()->with('success', 'Arriving at delivery location!');
    }

    /**
     * Transition to at dropoff location
     */
    public function atDropoff(DeliveryOrder $order)
    {
        // Authorization: Only assigned courier
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // Must be arriving or in transit
        if (!in_array($order->status, ['in_transit', 'arriving_at_dropoff'])) {
            return back()->with('error', 'Order must be in transit or arriving at dropoff.');
        }

        DB::transaction(function () use ($order) {
            try {
                $currentStatus = $order->status;
                
                // If coming from 'in_transit', transition through 'arriving_at_dropoff' first
                if ($currentStatus === 'in_transit') {
                    $order->transitionTo('arriving_at_dropoff');
                }
                
                $order->transitionTo('at_dropoff');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => $currentStatus,
                    'new_status' => 'at_dropoff',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'Arrived at delivery location',
                ]);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return back()->with('success', 'You are at the delivery location!');
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

        // Must be at dropoff
        if ($order->status !== 'at_dropoff') {
            return back()->with('error', 'You must be at the delivery location first.');
        }

        DB::transaction(function () use ($order) {
            try {
                $order->transitionTo('delivered');

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => 'at_dropoff',
                    'new_status' => 'delivered',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => 'Package delivered successfully',
                ]);

                // Record delivery location
                CourierLocationService::recordLocationForStatusUpdate(
                    $order,
                    'delivered',
                    address: 'Delivery complete at ' . ($order->delivery_address ?? 'delivery location')
                );
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return redirect()->route('courier.dashboard')
            ->with('success', 'Order delivered successfully! Great job!');
    }

    /**
     * Mark delivery as failed
     */
    public function deliveryFailed(Request $request, DeliveryOrder $order)
    {
        // Authorization: Only assigned courier
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // Can only fail from at_dropoff or arriving_at_dropoff
        if (!in_array($order->status, ['at_dropoff', 'arriving_at_dropoff', 'in_transit'])) {
            return back()->with('error', 'Delivery failure can only be recorded during delivery attempt.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($order, $validated) {
            try {
                $order->transitionTo('delivery_failed', [
                    'cancellation_reason' => $validated['cancellation_reason'],
                ]);

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => $order->getOriginal('status'),
                    'new_status' => 'delivery_failed',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => $validated['cancellation_reason'],
                ]);

                // Record failed delivery attempt location
                CourierLocationService::recordLocationForStatusUpdate(
                    $order,
                    'delivery_failed',
                    address: 'Delivery failed: ' . substr($validated['cancellation_reason'], 0, 50)
                );
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return back()->with('success', 'Delivery failure recorded. Next step: return to sender or retry.');
    }

    /**
     * Cancel order by courier
     * 
     * RESTRICTIONS:
     * - Can ONLY cancel from: accepted, arriving_at_pickup, at_pickup
     * - CANNOT cancel after: picked_up (item collected)
     */
    public function cancelOrder(Request $request, DeliveryOrder $order)
    {
        // Authorization: Only assigned courier can cancel
        if ($order->courier_id !== auth()->id()) {
            abort(403, 'This order is not assigned to you.');
        }

        // STRICT: Check if courier CAN cancel
        if (!$order->isCancellableByCourier()) {
            return back()->with('error', sprintf(
                'Cannot cancel order in "%s" status. Couriers can only cancel before pickup. ' .
                'Allowed statuses: accepted, arriving_at_pickup, at_pickup.',
                str_replace('_', ' ', ucfirst($order->status))
            ))->withInput();
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($order, $validated) {
            try {
                $oldStatus = $order->status;
                $order->cancelByCourier($validated['cancellation_reason']);

                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'cancelled_by_courier',
                    'changed_by' => auth()->id(),
                    'actor_type' => 'courier',
                    'reason' => $validated['cancellation_reason'],
                ]);

                // Record cancellation location
                CourierLocationService::recordLocationForStatusUpdate(
                    $order,
                    'cancelled_by_courier',
                    address: 'Cancellation: ' . substr($validated['cancellation_reason'], 0, 50)
                );

                // Clear courier assignment
                $order->update(['courier_id' => null]);
                
                // Return order to 'awaiting_courier' so other couriers can accept
                $order->update(['status' => 'awaiting_courier']);
            } catch (\InvalidArgumentException $e) {
                throw new \Exception($e->getMessage());
            }
        });

        return redirect()->route('courier.dashboard')
            ->with('success', 'Order cancelled. It is now available for other couriers.');
    }

    /**
     * Show courier profile page
     */
    public function showProfile()
    {
        return view('courier.profile');
    }

    /**
     * Update courier profile information
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
        ]);

        auth()->user()->update($validated);

        return redirect()->route('courier.profile')->with('success', 'Profile updated successfully!');
    }

    /**
     * Update courier password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return redirect()->route('courier.profile')->with('success', 'Password updated successfully!');
    }
}
