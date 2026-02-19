<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\CourierLocation;
use Illuminate\Support\Facades\Http;

class CourierLocationService
{
    /**
     * Record courier location when status changes
     * Optionally geocode latitude/longitude from address
     */
    public static function recordLocationForStatusUpdate(
        DeliveryOrder $order,
        string $newStatus,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $address = null
    ): CourierLocation {
        // If we have lat/lng, use them; otherwise try to geocode the address
        if ($latitude === null || $longitude === null) {
            if ($address) {
                $coords = self::geocodeAddress($address);
                if ($coords) {
                    $latitude = $coords['lat'];
                    $longitude = $coords['lng'];
                }
            }

            // If still missing, fall back to request values or a safe default
            if ($latitude === null || $longitude === null) {
                $latitude = request()->input('latitude', 14.5995); // Default fallback location (Manila, Philippines)
                $longitude = request()->input('longitude', 120.9842);

                if ($address === null) {
                    $address = request()->input('address', 'Location Not Specified');
                }
            }
        }

        $courierLocation = CourierLocation::create([
            'delivery_order_id' => $order->id,
            'courier_id' => auth()->id(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'address' => $address ?? 'Location Updated',
            'status_at_location' => $newStatus,
            'notes' => "Status changed to " . str_replace('_', ' ', $newStatus),
        ]);

        return $courierLocation;
    }

    /**
     * Geocode address using Nominatim (OpenStreetMap)
     * Returns ['lat' => float, 'lng' => float] or null if not found
     */
    public static function geocodeAddress(string $address): ?array
    {
        try {
            $response = Http::timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
            ]);

            if ($response->successful() && $response->json()) {
                $result = $response->json()[0] ?? null;
                if ($result) {
                    return [
                        'lat' => (float)$result['lat'],
                        'lng' => (float)$result['lon'],
                    ];
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail - use defaults
            \Log::warning('Geocoding failed for address: ' . $address, ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Calculate delivery fee based on distance
     * Uses OSRM (Open Source Routing Machine) for distance calculation
     */
    public static function calculateDeliveryFee(
        string $pickupAddress,
        string $deliveryAddress,
        float $baseRate = 5.00,
        float $perKmRate = 0.50
    ): ?float {
        // First geocode both addresses
        $pickupCoords = self::geocodeAddress($pickupAddress);
        $deliveryCoords = self::geocodeAddress($deliveryAddress);

        if (!$pickupCoords || !$deliveryCoords) {
            return null; // Cannot calculate without coordinates
        }

        // Use OSRM to get distance
        $distance = self::getDistance(
            $pickupCoords['lat'],
            $pickupCoords['lng'],
            $deliveryCoords['lat'],
            $deliveryCoords['lng']
        );

        if ($distance === null) {
            return null;
        }

        // Calculate fee: base rate + per km rate
        $distanceInKm = $distance / 1000;
        $fee = $baseRate + ($distanceInKm * $perKmRate);

        return round($fee, 2);
    }

    /**
     * Get distance between two coordinates using OSRM
     * Returns distance in meters or null if error
     */
    public static function getDistance(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng
    ): ?float {
        try {
            // Using public OSRM API
            $response = Http::timeout(5)->get("https://router.project-osrm.org/route/v1/driving/{$fromLng},{$fromLat};{$toLng},{$toLat}", [
                'overview' => 'false',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0]['distance'])) {
                    return (float)$data['routes'][0]['distance'];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('OSRM distance calculation failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get route coordinates between two points
     * Returns array of [lat, lng] coordinates or null
     */
    public static function getRouteCoordinates(
        float $fromLat,
        float $fromLng,
        float $toLat,
        float $toLng
    ): ?array {
        try {
            $response = Http::timeout(5)->get("https://router.project-osrm.org/route/v1/driving/{$fromLng},{$fromLat};{$toLng},{$toLat}", [
                'overview' => 'full',
                'geometries' => 'geojson',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0]['geometry']['coordinates'])) {
                    // Convert from [lng, lat] to [lat, lng]
                    return array_map(function ($coord) {
                        return [$coord[1], $coord[0]];
                    }, $data['routes'][0]['geometry']['coordinates']);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('OSRM route calculation failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
