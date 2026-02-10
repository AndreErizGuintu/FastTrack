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
        'reason',
        'notes',
        'location_lat',
        'location_lng',
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
}
