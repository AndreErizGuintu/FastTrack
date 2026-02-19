<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierLocation extends Model
{
    const UPDATED_AT = null; // Only created_at, no updates
    
    protected $fillable = [
        'delivery_order_id',
        'courier_id',
        'latitude',
        'longitude',
        'address',
        'status_at_location',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
    ];

    /**
     * Get the delivery order this location belongs to
     */
    public function deliveryOrder(): BelongsTo
    {
        return $this->belongsTo(DeliveryOrder::class);
    }

    /**
     * Get the courier at this location
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Scope: Get locations for an order
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('delivery_order_id', $orderId)->latest('created_at');
    }

    /**
     * Get coordinates as array for map
     */
    public function getCoordinates(): array
    {
        return [
            'lat' => (float)$this->latitude,
            'lng' => (float)$this->longitude,
        ];
    }

    /**
     * Get location info as array
     */
    public function toMapData(): array
    {
        return [
            'id' => $this->id,
            'lat' => (float)$this->latitude,
            'lng' => (float)$this->longitude,
            'address' => $this->address,
            'status' => $this->status_at_location,
            'time' => $this->created_at->format('h:i A'),
            'timestamp' => $this->created_at->timestamp,
        ];
    }
}
