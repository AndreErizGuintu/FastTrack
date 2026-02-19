<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class DeliveryOrder extends Model
{
    // ==================== FILLABLE & CASTS ====================
    
    protected $fillable = [
        'user_id',
        'courier_id',
        'tracking_id',
        'product_description',
        'estimated_weight',
        'special_notes',
        'pickup_address',
        'pickup_contact_phone',
        'pickup_lat',
        'pickup_lng',
        'delivery_address',
        'delivery_contact_phone',
        'delivery_lat',
        'delivery_lng',
        'status',
        'accepted_at',
        'arriving_at_pickup_at',
        'at_pickup_at',
        'picked_up_at',
        'arriving_at_dropoff_at',
        'at_dropoff_at',
        'delivery_failed_at',
        'returned_at',
        'expired_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'delivery_fee',
    ];

    protected $casts = [
        'estimated_weight' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'accepted_at' => 'datetime',
        'arriving_at_pickup_at' => 'datetime',
        'at_pickup_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'arriving_at_dropoff_at' => 'datetime',
        'at_dropoff_at' => 'datetime',
        'delivery_failed_at' => 'datetime',
        'returned_at' => 'datetime',
        'expired_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ==================== MODEL BOOT ====================

    /**
     * Boot the model
     * Generate unique tracking ID when order is created
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->tracking_id)) {
                $order->tracking_id = self::generateTrackingId();
            }
        });
    }

    /**
     * Generate unique tracking ID
     * Format: FT-YYYYMM-XXXXX (e.g., FT-202602-A3K9L)
     */
    private static function generateTrackingId(): string
    {
        do {
            $yearMonth = date('Ym');
            $randomCode = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5));
            $trackingId = "FT-{$yearMonth}-{$randomCode}";
            
            // Check if tracking ID already exists
            $exists = self::where('tracking_id', $trackingId)->exists();
        } while ($exists);

        return $trackingId;
    }

    // ==================== STATE MACHINE DEFINITIONS ====================

    /**
     * Valid status transitions for finite state machine
     * Format: 'from_status' => ['allowed_to_status_1', 'allowed_to_status_2']
     */
    private const ALLOWED_TRANSITIONS = [
        'draft' => [
            'awaiting_courier',      // Customer confirms order
            'cancelled_by_user',     // Customer cancels before posting
        ],
        
        'awaiting_courier' => [
            'accepted',              // Courier accepts
            'cancelled_by_user',     // Customer cancels
            'expired',               // System timeout (no courier acceptance)
        ],
        
        'courier_assigned' => [
            'accepted',              // Courier manually accepts
            'cancelled_by_system',   // System cancels assignment
        ],
        
        'accepted' => [
            'arriving_at_pickup',    // Courier heads to pickup
            'cancelled_by_user',     // User cancels *only* if not picked up
            'cancelled_by_courier',  // Courier cancels before pickup
        ],
        
        'arriving_at_pickup' => [
            'at_pickup',             // Courier arrived at pickup
            'cancelled_by_courier',  // Courier cancels en route
        ],
        
        'at_pickup' => [
            'picked_up',             // Courier collected item
            'cancelled_by_courier',  // Courier cancels at location
        ],
        
        'picked_up' => [
            'in_transit',            // Item on the way
            'delivery_failed',       // Delivery failed (no receiver)
        ],
        
        'in_transit' => [
            'arriving_at_dropoff',   // Courier near destination
            'delivery_failed',       // Issue en route
        ],
        
        'arriving_at_dropoff' => [
            'at_dropoff',            // Courier at delivery location
            'delivery_failed',       // Cannot access location
        ],
        
        'at_dropoff' => [
            'delivered',             // Successfully delivered
            'delivery_failed',       // Delivery refused/failed
        ],
        
        'delivered' => [
            'returned',              // Item returned (if applicable)
        ],
        
        'delivery_failed' => [
            'returned',              // Return to sender
            'in_transit',            // Retry delivery
        ],
        
        'returned' => [],            // Terminal state
        
        'cancelled_by_user' => [],   // Terminal state
        'cancelled_by_courier' => [], // Terminal state
        'cancelled_by_system' => [],  // Terminal state
        'expired' => [],              // Terminal state
    ];

    /**
     * Terminal states - no further transitions allowed
     */
    private const TERMINAL_STATES = [
        'delivered',
        'cancelled_by_user',
        'cancelled_by_courier',
        'cancelled_by_system',
        'expired',
        'returned',
    ];

    /**
     * Statuses that allow user cancellation
     */
    private const USER_CANCELLABLE_STATES = [
        'draft',
        'awaiting_courier',
        'accepted',
    ];

    /**
     * Statuses that allow courier cancellation
     */
    private const COURIER_CANCELLABLE_STATES = [
        'accepted',
        'arriving_at_pickup',
        'at_pickup',
    ];

    // ==================== BOOT & EVENTS ====================

    /**
     * Boot method - Auto-log status changes to order_status_history
     */
    protected static function booted()
    {
        static::updated(function ($order) {
            if ($order->isDirty('status')) {
                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => $order->getOriginal('status'),
                    'new_status' => $order->status,
                    'changed_by' => Auth::id() ?? 1,
                    'actor_type' => 'system', // Will be overridden by controller
                ]);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the user who created this order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the courier assigned to this order
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Get all messages for this order
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all status history records for this order
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Get all courier location history for this order
     */
    public function courierLocations(): HasMany
    {
        return $this->hasMany(CourierLocation::class)->latest('created_at');
    }

    /**
     * Scope: Get only pending orders
     */
    public function scopePending($query)
    {
        // Pending in the new lifecycle means orders awaiting a courier
        return $query->where('status', 'awaiting_courier');
    }

    /**
     * Scope: Get only active orders (not delivered or cancelled)
     */
    public function scopeActive($query)
    {
        // Active = any non-terminal status
        return $query->whereNotIn('status', self::TERMINAL_STATES);
    }

    /**
     * Scope: Get orders for a specific courier
     */
    public function scopeForCourier($query, $courierId)
    {
        return $query->where('courier_id', $courierId);
    }

    /**
     * Scope: Get orders for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if chat is active for this order
     */
    public function isChatActive(): bool
    {
        return in_array($this->status, ['accepted', 'in_transit', 'arriving_at_pickup', 'at_pickup', 'picked_up', 'arriving_at_dropoff', 'at_dropoff']);
    }

    /**
     * Check if order is completed (delivered or cancelled)
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, self::TERMINAL_STATES);
    }

    // ==================== STATE MACHINE METHODS ====================

    /**
     * Check if order is in a terminal state
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATES);
    }

    /**
     * Check if order is still active (not completed)
     */
    public function isActive(): bool
    {
        return !$this->isTerminal();
    }

    /**
     * Check if order can be cancelled by the customer (user)
     */
    public function isCancellableByUser(): bool
    {
        return in_array($this->status, self::USER_CANCELLABLE_STATES);
    }

    /**
     * Check if order can be cancelled by the courier
     */
    public function isCancellableByCourier(): bool
    {
        return in_array($this->status, self::COURIER_CANCELLABLE_STATES);
    }

    /**
     * Validate and transition to a new status
     * 
     * @param string $newStatus The target status
     * @param array $data Additional data (timestamps, etc.)
     * @throws \InvalidArgumentException if transition is not allowed
     */
    public function transitionTo(string $newStatus, array $data = []): self
    {
        if (!$this->canTransitionTo($newStatus)) {
            $currentStatus = $this->status;
            $validTransitions = $this->getValidTransitions();
            
            throw new \InvalidArgumentException(
                "Cannot transition from '{$currentStatus}' to '{$newStatus}'. " .
                "Valid transitions: " . implode(', ', $validTransitions)
            );
        }

        // Auto-set timestamp based on status
        $timestamps = $data + $this->getTimestampsForStatus($newStatus);
        
        $this->update([
            'status' => $newStatus,
            ...$timestamps,
        ]);

        return $this;
    }

    /**
     * Check if transition to a status is allowed
     */
    public function canTransitionTo(string $newStatus): bool
    {
        // Cannot transition FROM terminal states
        if ($this->isTerminal()) {
            return false;
        }

        $validTransitions = self::ALLOWED_TRANSITIONS[$this->status] ?? [];
        return in_array($newStatus, $validTransitions);
    }

    /**
     * Get valid transitions from current status
     */
    public function getValidTransitions(): array
    {
        if ($this->isTerminal()) {
            return [];
        }

        return self::ALLOWED_TRANSITIONS[$this->status] ?? [];
    }

    /**
     * Auto-populate timestamps based on status
     */
    private function getTimestampsForStatus(string $status): array
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

    // ==================== ACTION METHODS (Deprecated - Use transitionTo) ====================

    /**
     * Accept order by courier
     * @deprecated Use transitionTo('accepted') instead
     */
    public function acceptByCourier($courierId): void
    {
        if (!$this->canTransitionTo('accepted')) {
            throw new \InvalidArgumentException("Order cannot be accepted from status: {$this->status}");
        }

        $this->update([
            'courier_id' => $courierId,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark order as picked up (transition to in_transit)
     * @deprecated Use transitionTo('picked_up') then transitionTo('in_transit')
     */
    public function markPickedUp(): void
    {
        if ($this->status !== 'accepted') {
            throw new \InvalidArgumentException("Order must be in 'accepted' status to mark as picked up");
        }

        $this->transitionTo('picked_up');
        $this->transitionTo('in_transit');
    }

    /**
     * Mark order as delivered
     * @deprecated Use transitionTo('delivered')
     */
    public function markDelivered(): void
    {
        if ($this->status !== 'in_transit') {
            throw new \InvalidArgumentException("Order must be in 'in_transit' status to mark as delivered");
        }

        $this->transitionTo('delivered');
    }

    /**
     * Cancel order with reason
     * @deprecated Use cancelByUser() or cancelByCourier()
     */
    public function cancel(string $reason): void
    {
        if ($this->isCancellableByUser()) {
            $this->cancelByUser($reason);
        } else {
            throw new \InvalidArgumentException("Order in status '{$this->status}' cannot be cancelled");
        }
    }

    // ==================== NEW CANCELLATION METHODS ====================

    /**
     * Cancel order by customer (user)
     */
    public function cancelByUser(string $reason): void
    {
        if (!$this->isCancellableByUser()) {
            throw new \InvalidArgumentException(
                "Order in status '{$this->status}' cannot be cancelled by user. " .
                "Can only be cancelled from: " . implode(', ', self::USER_CANCELLABLE_STATES)
            );
        }

        $this->transitionTo('cancelled_by_user', [
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Cancel order by courier
     */
    public function cancelByCourier(string $reason): void
    {
        if (!$this->isCancellableByCourier()) {
            throw new \InvalidArgumentException(
                "Order in status '{$this->status}' cannot be cancelled by courier. " .
                "Can only be cancelled from: " . implode(', ', self::COURIER_CANCELLABLE_STATES)
            );
        }

        $this->transitionTo('cancelled_by_courier', [
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Cancel order by system (automated)
     */
    public function cancelBySystem(string $reason): void
    {
        if ($this->isTerminal()) {
            throw new \InvalidArgumentException(
                "Cannot cancel order in terminal status '{$this->status}'"
            );
        }

        $this->transitionTo('cancelled_by_system', [
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Mark order as expired (no courier acceptance within timeout)
     */
    public function markExpired(string $reason = 'No courier accepted order within time limit'): void
    {
        if ($this->status !== 'awaiting_courier') {
            throw new \InvalidArgumentException(
                "Only orders in 'awaiting_courier' status can expire. Current: {$this->status}"
            );
        }

        $this->transitionTo('expired', [
            'cancellation_reason' => $reason,
        ]);
    }
}