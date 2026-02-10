<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryOrder extends Model
{
    protected $fillable = [
        'user_id',
        'courier_id',
        'product_description',
        'estimated_weight',
        'special_notes',
        'pickup_address',
        'pickup_contact_phone',
        'delivery_address',
        'delivery_contact_phone',
        'status',
        'accepted_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'delivery_fee',
    ];

    protected $casts = [
        'estimated_weight' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

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
                    'changed_by' => auth()->id(),
                ]);
            }
        });
    }

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
     * Scope: Get only pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get only active orders (not delivered or cancelled)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['delivered', 'cancelled']);
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
        return in_array($this->status, ['accepted', 'in_transit']);
    }

    /**
     * Check if order is completed (delivered or cancelled)
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled']);
    }

    /**
     * Accept order by courier
     */
    public function acceptByCourier($courierId): void
    {
        $this->update([
            'courier_id' => $courierId,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark order as picked up
     */
    public function markPickedUp(): void
    {
        $this->update([
            'status' => 'in_transit',
            'picked_up_at' => now(),
        ]);
    }

    /**
     * Mark order as delivered
     */
    public function markDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Cancel order with reason
     */
    public function cancel(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }
}
