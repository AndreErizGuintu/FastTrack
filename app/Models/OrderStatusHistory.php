<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    protected $table = 'order_status_history';
    const UPDATED_AT = null; // This table only has created_at

    protected $fillable = [
        'delivery_order_id',
        'old_status',
        'new_status',
        'changed_by',
        'actor_type',
        'reason',
        'notes',
        'location_lat',
        'location_lng',
        'seen_at',
    ];

    protected $casts = [
        'seen_at' => 'datetime',
    ];

    /**
     * Actor types enum
     */
    public const ACTOR_TYPES = [
        'user',    // Customer (order creator)
        'courier', // Delivery person
        'system',  // Automated system (expiration, timeout, etc.)
        'admin',   // Administrator
    ];

    /**
     * Get the delivery order this history belongs to
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    /**
     * Get the user who made this status change
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope: Get history for a specific order
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('delivery_order_id', $orderId);
    }

    /**
     * Scope: Get history by actor type
     */
    public function scopeByActorType($query, string $actorType)
    {
        return $query->where('actor_type', $actorType);
    }

    /**
     * Check if this change was made by a courier
     */
    public function isCourierAction(): bool
    {
        return $this->actor_type === 'courier';
    }

    /**
     * Check if this change was made by a user
     */
    public function isUserAction(): bool
    {
        return $this->actor_type === 'user';
    }

    /**
     * Check if this change was made by system
     */
    public function isSystemAction(): bool
    {
        return $this->actor_type === 'system';
    }
}

