<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DeliveryOrder;
use App\Models\CourierLocation;
use App\Models\OrderStatusHistory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create or get test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User']
        );

        // Create or get test courier
        $courier = User::firstOrCreate(
            ['email' => 'courier@example.com'],
            ['name' => 'Demo Courier', 'role' => 'courier']
        );

        // Create a demo delivered order with simulated courier locations
        $order = DeliveryOrder::firstOrCreate(
            ['tracking_id' => 'FT-202602-DEMO1'],
            [
                'user_id' => $user->id,
                'courier_id' => $courier->id,
                'status' => 'delivered',
                'product_description' => 'Electronics Package - Demo Order',
                'estimated_weight' => 2.5,
                'special_notes' => 'Fragile items, handle with care',
                'pickup_address' => 'SM Mall of Asia, Entertainment City, Pasay City, 1300, Philippines',
                'pickup_contact_phone' => '+63-995-123-4567',
                'delivery_address' => 'BGC Central Post Office, Taguig, Metro Manila, Philippines',
                'delivery_contact_phone' => '+63-917-987-6543',
                'delivery_fee' => 150.00,
                'accepted_at' => now()->subHours(3),
                'arriving_at_pickup_at' => now()->subHours(2.8),
                'at_pickup_at' => now()->subHours(2.7),
                'picked_up_at' => now()->subHours(2.5),
                'arriving_at_dropoff_at' => now()->subHours(1.2),
                'at_dropoff_at' => now()->subHours(1),
                'delivered_at' => now()->subHours(0.5),
            ]
        );

        // Simulate courier journey with multiple location updates
        $journeySteps = [
            [
                'status' => 'accepted',
                'address' => 'MOA Complex, Pasay City',
                'latitude' => 14.5545,
                'longitude' => 120.9163,
                'time_offset' => 3, // hours ago
            ],
            [
                'status' => 'arriving_at_pickup',
                'address' => 'SM Mall of Asia, Pasay City',
                'latitude' => 14.5518,
                'longitude' => 120.9155,
                'time_offset' => 2.8,
            ],
            [
                'status' => 'at_pickup',
                'address' => 'SM Mall of Asia Entrance, Pasay City',
                'latitude' => 14.5520,
                'longitude' => 120.9160,
                'time_offset' => 2.7,
            ],
            [
                'status' => 'picked_up',
                'address' => 'SM Mall of Asia, Pasay City',
                'latitude' => 14.5520,
                'longitude' => 120.9160,
                'time_offset' => 2.5,
            ],
            [
                'status' => 'in_transit',
                'address' => 'EDSA, Makati City',
                'latitude' => 14.5600,
                'longitude' => 120.9850,
                'time_offset' => 2.0,
            ],
            [
                'status' => 'in_transit',
                'address' => 'Ayala Avenue, Makati City',
                'latitude' => 14.5650,
                'longitude' => 121.0100,
                'time_offset' => 1.5,
            ],
            [
                'status' => 'arriving_at_dropoff',
                'address' => 'BGC Area, Taguig City',
                'latitude' => 14.5560,
                'longitude' => 121.0335,
                'time_offset' => 1.2,
            ],
            [
                'status' => 'at_dropoff',
                'address' => 'BGC Central Post Office, Taguig',
                'latitude' => 14.5562,
                'longitude' => 121.0338,
                'time_offset' => 1.0,
            ],
            [
                'status' => 'delivered',
                'address' => 'BGC Central Post Office, Taguig',
                'latitude' => 14.5562,
                'longitude' => 121.0338,
                'time_offset' => 0.5,
            ],
        ];

        // Create courier location records for each step
        // Only create if none exist for this order yet
        if ($order->courierLocations()->count() === 0) {
            foreach ($journeySteps as $step) {
                CourierLocation::create([
                    'delivery_order_id' => $order->id,
                    'courier_id' => $courier->id,
                    'latitude' => $step['latitude'],
                    'longitude' => $step['longitude'],
                    'address' => $step['address'],
                    'status_at_location' => $step['status'],
                    'notes' => "Status: " . str_replace('_', ' ', $step['status']),
                    'created_at' => now()->subHours($step['time_offset']),
                ]);

                // Create status history
                OrderStatusHistory::create([
                    'delivery_order_id' => $order->id,
                    'old_status' => null,
                    'new_status' => $step['status'],
                    'changed_by' => $courier->id,
                    'actor_type' => 'courier',
                    'reason' => str_replace('_', ' ', $step['status']),
                    'created_at' => now()->subHours($step['time_offset']),
                ]);
            }
        }
    }
}
