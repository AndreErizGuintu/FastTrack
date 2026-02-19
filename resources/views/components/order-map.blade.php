<div id="orderMap" class="w-full rounded-xl shadow-lg border border-gray-200" style="height: 500px;"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />

<style>
    .leaflet-container {
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .map-marker-icon {
        font-size: 24px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    .location-popup {
        font-size: 12px;
        min-width: 200px;
    }
    .location-popup strong {
        display: block;
        margin-bottom: 4px;
        color: #1f2937;
    }
    .location-popup .time {
        color: #6b7280;
        font-size: 11px;
        margin-top: 4px;
    }
</style>

<script>
    // Run immediately, not on DOMContentLoaded, since this may be loaded dynamically
    (function() {
        // Wait a tick for the DOM to be ready
        setTimeout(() => {
            const orderData = {!! json_encode([
                'pickup_address' => $order->pickup_address,
                'delivery_address' => $order->delivery_address,
                'status' => $order->status,
                'locations' => $order->courierLocations->map(fn($loc) => $loc->toMapData())->values()
            ]) !!};

        // Initialize map
        const map = L.map('orderMap').setView([0, 0], 5);
        
        // Ensure map sizes properly
        map.invalidateSize();
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);

        const locations = orderData.locations;
        const bounds = L.latLngBounds();

        // Status colors and icons
        const statusConfig = {
            'accepted': { icon: '📍', color: '#3b82f6' },
            'arriving_at_pickup': { icon: '🚗', color: '#8b5cf6' },
            'at_pickup': { icon: '📦', color: '#ec4899' },
            'picked_up': { icon: '📤', color: '#8b5cf6' },
            'in_transit': { icon: '🚚', color: '#f59e0b' },
            'arriving_at_dropoff': { icon: '📍', color: '#f59e0b' },
            'at_dropoff': { icon: '🏠', color: '#06b6d4' },
            'delivered': { icon: '✅', color: '#10b981' },
        };

        // Geocode and add pickup marker
        if (orderData.pickup_address) {
            geocodeAndAddMarker(orderData.pickup_address, '🟢', '#10b981', 'Pickup Location', true);
        }

        // Geocode and add delivery marker
        if (orderData.delivery_address) {
            geocodeAndAddMarker(orderData.delivery_address, '🔴', '#ef4444', 'Delivery Location', false);
        }

        // Add courier location markers
        locations.forEach((location, index) => {
            const config = statusConfig[location.status] || { icon: '📍', color: '#6b7280' };
            const isLatest = index === locations.length - 1;
            const markerSize = isLatest ? 40 : 32;

            // Create custom marker with HTML
            const markerHtml = `
                <div style="
                    width: ${markerSize}px;
                    height: ${markerSize}px;
                    background: ${config.color};
                    border: 3px solid white;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: ${markerSize * 0.6}px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                    cursor: pointer;
                    transition: all 0.2s ease;
                ">
                    ${config.icon}
                </div>
            `;

            const icon = L.divIcon({
                html: markerHtml,
                iconSize: [markerSize, markerSize],
                className: 'map-marker-icon'
            });

            const marker = L.marker([location.lat, location.lng], { icon })
                .addTo(map)
                .bindPopup(`
                    <div class="location-popup">
                        <strong>${location.address || 'Location'}</strong>
                        <div class="text-gray-700">Status: ${location.status.replace(/_/g, ' ')}</div>
                        <div class="time">📅 ${location.time}</div>
                    </div>
                `);

            bounds.extend([location.lat, location.lng]);
        });

        // Geocode address and add marker
        async function geocodeAndAddMarker(address, icon, color, label, isPickup) {
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`);
                const data = await response.json();
                
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    
                    const markerHtml = `
                        <div style="
                            width: 45px;
                            height: 45px;
                            background: ${color};
                            border: 4px solid white;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 24px;
                            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                            cursor: pointer;
                        ">
                            ${icon}
                        </div>
                    `;

                    const markerIcon = L.divIcon({
                        html: markerHtml,
                        iconSize: [45, 45],
                        className: 'destination-marker'
                    });

                    L.marker([lat, lng], { icon: markerIcon })
                        .addTo(map)
                        .bindPopup(`
                            <div class="location-popup">
                                <strong>${label}</strong>
                                <div class="text-gray-700">${address}</div>
                            </div>
                        `);

                    bounds.extend([lat, lng]);
                    
                    // Fit map to bounds after adding all markers
                    if (bounds.isValid()) {
                        map.fitBounds(bounds, { padding: [50, 50] });
                    }
                }
            } catch (error) {
                console.error('Geocoding failed for:', address, error);
            }
        }

        // Fit map to bounds if we have locations
        if (locations.length > 0 || bounds.isValid()) {
            setTimeout(() => {
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
            }, 500);
        } else {
            // Default to world view if no locations
            map.setView([20, 0], 2);
        }

        // Add route line connecting all courier locations
        if (locations.length > 1) {
            const routeCoordinates = locations.map(loc => [loc.lat, loc.lng]);
            const polyline = L.polyline(routeCoordinates, {
                color: '#ef4444',
                weight: 3,
                opacity: 0.7,
                dashArray: '5, 5'
            }).addTo(map);
        }

        // Add pickup and delivery info (if available from geocoding, for now show addresses)
        L.control.attribution().setPrefix('').addTo(map);
        }, 100); // Wait for DOM to be ready
    })();
</script>
