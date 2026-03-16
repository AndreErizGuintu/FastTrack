<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\DeliveryOrder;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private const ORDER_STATUSES = [
        'draft',
        'awaiting_courier',
        'courier_assigned',
        'accepted',
        'arriving_at_pickup',
        'at_pickup',
        'picked_up',
        'in_transit',
        'arriving_at_dropoff',
        'at_dropoff',
        'delivered',
        'delivery_failed',
        'returned',
        'cancelled_by_user',
        'cancelled_by_courier',
        'cancelled_by_system',
        'expired',
    ];

    /**
     * Display the admin dashboard.
     */
    public function dashboard()
    {
        $totalProducts = Product::count();
        $totalUsers = User::where('role', 'user')->count();
        $totalCouriers = User::where('role', 'courier')->count();
        $totalOrders = DeliveryOrder::count();
        $activeOrders = DeliveryOrder::whereNotIn('status', [
            'delivered',
            'cancelled_by_user',
            'cancelled_by_courier',
            'cancelled_by_system',
            'expired',
            'returned',
        ])->count();
        $deliveredWithProof = DeliveryOrder::where('status', 'delivered')
            ->whereNotNull('pod_image_path')
            ->count();
        $deliveredWithoutProof = DeliveryOrder::where('status', 'delivered')
            ->whereNull('pod_image_path')
            ->count();
        $recentOrders = DeliveryOrder::with(['user:id,name', 'courier:id,name'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalUsers',
            'totalCouriers',
            'totalOrders',
            'activeOrders',
            'deliveredWithProof',
            'deliveredWithoutProof',
            'recentOrders'
        ));
    }

    /**
     * Display a listing of all users.
     */
    public function userIndex()
    {
        $search = trim((string) request('search', ''));
        $role = (string) request('role', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->when(in_array($role, ['user', 'admin', 'courier'], true), function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalCouriers = User::where('role', 'courier')->count();

        return view('admin.index', compact(
            'users',
            'totalUsers',
            'totalAdmins',
            'totalCouriers',
            'search',
            'role'
        ));
    }

    /**
     * Display the form for editing the specified user.
     */
    public function userEdit(User $user)
    {
        return view('admin.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function userUpdate(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin,courier',
        ]);

        // Guardrail: admin cannot demote self
        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->withInput()->with('error', 'You cannot remove your own admin role.');
        }

        // Guardrail: prevent removing the last admin
        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withInput()->with('error', 'At least one admin account must remain in the system.');
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Display a listing of orders for admin monitoring.
     */
    public function orderIndex(Request $request)
    {
        $status = (string) $request->query('status', '');
        $tracking = trim((string) $request->query('tracking_id', ''));
        $proof = (string) $request->query('proof', '');

        $orders = DeliveryOrder::with(['user:id,name', 'courier:id,name'])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($tracking !== '', function ($query) use ($tracking) {
                $query->where('tracking_id', 'like', '%' . $tracking . '%');
            })
            ->when($proof === 'with', function ($query) {
                $query->whereNotNull('pod_image_path');
            })
            ->when($proof === 'without', function ($query) {
                $query->whereNull('pod_image_path');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => self::ORDER_STATUSES,
            'filters' => [
                'status' => $status,
                'tracking_id' => $tracking,
                'proof' => $proof,
            ],
        ]);
    }

    /**
     * Show specific order details for admin.
     */
    public function orderShow(DeliveryOrder $order)
    {
        $order->load([
            'user:id,name,email',
            'courier:id,name,email',
            'statusHistory.changedBy:id,name',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => self::ORDER_STATUSES,
        ]);
    }

    /**
     * Update an order status by admin with audit trail.
     */
    public function orderUpdateStatus(Request $request, DeliveryOrder $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', self::ORDER_STATUSES)],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $newStatus = $validated['status'];
        $reason = trim($validated['reason']);
        $oldStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return back()->with('error', 'Selected status is the same as the current status.');
        }

        if ($order->canTransitionTo($newStatus)) {
            $transitionData = in_array($newStatus, ['cancelled_by_user', 'cancelled_by_courier', 'cancelled_by_system', 'expired', 'delivery_failed'], true)
                ? ['cancellation_reason' => $reason]
                : [];

            $order->transitionTo($newStatus, $transitionData);
        } else {
            $order->update(array_merge(
                [
                    'status' => $newStatus,
                ],
                $this->getStatusTimestampData($newStatus),
                in_array($newStatus, ['cancelled_by_user', 'cancelled_by_courier', 'cancelled_by_system', 'expired', 'delivery_failed'], true)
                    ? ['cancellation_reason' => $reason]
                    : []
            ));
        }

        OrderStatusHistory::create([
            'delivery_order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => auth()->id(),
            'actor_type' => 'admin',
            'reason' => $reason,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }

    private function getStatusTimestampData(string $status): array
    {
        return match ($status) {
            'accepted' => ['accepted_at' => now()],
            'arriving_at_pickup' => ['arriving_at_pickup_at' => now()],
            'at_pickup' => ['at_pickup_at' => now()],
            'picked_up' => ['picked_up_at' => now()],
            'arriving_at_dropoff' => ['arriving_at_dropoff_at' => now()],
            'at_dropoff' => ['at_dropoff_at' => now()],
            'delivered' => ['delivered_at' => now()],
            'delivery_failed' => ['delivery_failed_at' => now()],
            'returned' => ['returned_at' => now()],
            'expired' => ['expired_at' => now()],
            'cancelled_by_user', 'cancelled_by_courier', 'cancelled_by_system' => ['cancelled_at' => now()],
            default => [],
        };
    }
}
