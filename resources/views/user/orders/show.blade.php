<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | FastTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-gradient-to-r from-red-700 to-red-900 text-white shadow-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-shipping-fast text-xl"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">FastTrack</span>
                </div>
                
                <div class="flex items-center space-x-6">
                    <a href="{{ route('user.orders.index') }}" class="bg-white text-red-700 hover:bg-red-50 px-5 py-2 rounded-full font-bold transition shadow-md text-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Order #{{ $order->id }}</h1>
                    <p class="text-gray-500">Created {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'yellow',
                        'accepted' => 'blue',
                        'in_transit' => 'purple',
                        'delivered' => 'emerald',
                        'cancelled' => 'red',
                    ];
                    $color = $statusColors[$order->status] ?? 'gray';
                @endphp
                <span class="bg-{{ $color }}-100 text-{{ $color }}-800 px-6 py-3 rounded-full text-sm font-bold uppercase">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Product Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-box text-red-600 mr-2"></i>Product Information
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Description</p>
                            <p class="text-gray-900">{{ $order->product_description }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Weight</p>
                                <p class="text-gray-900">{{ $order->estimated_weight }} kg</p>
                            </div>
                            @if($order->delivery_fee)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold mb-1">Delivery Fee</p>
                                    <p class="text-gray-900 font-bold text-emerald-600">${{ number_format($order->delivery_fee, 2) }}</p>
                                </div>
                            @endif
                        </div>
                        @if($order->special_notes)
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-xs text-yellow-800 uppercase font-bold mb-1">Special Notes</p>
                                <p class="text-sm text-yellow-900">{{ $order->special_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Location Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-red-600 mr-2"></i>Location Details
                    </h2>
                    <div class="space-y-4">
                        <div class="border-l-4 border-emerald-500 pl-4 py-2">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pickup Address</p>
                            <p class="text-gray-900 mb-1">{{ $order->pickup_address }}</p>
                            <p class="text-sm text-gray-600"><i class="fas fa-phone mr-1"></i>{{ $order->pickup_contact_phone }}</p>
                        </div>
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Delivery Address</p>
                            <p class="text-gray-900 mb-1">{{ $order->delivery_address }}</p>
                            <p class="text-sm text-gray-600"><i class="fas fa-phone mr-1"></i>{{ $order->delivery_contact_phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status History -->
                @if($order->statusHistory->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-history text-red-600 mr-2"></i>Order History
                        </h2>
                        <div class="space-y-3">
                            @foreach($order->statusHistory->sortByDesc('created_at') as $history)
                                <div class="flex items-start space-x-3 pb-3 border-b border-gray-100 last:border-0">
                                    <div class="bg-gray-100 rounded-full p-2 mt-1">
                                        <i class="fas fa-arrow-right text-gray-600 text-xs"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900">
                                            Changed from <span class="text-red-600">{{ $history->old_status ?? 'new' }}</span> to <span class="text-emerald-600">{{ $history->new_status }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            by {{ $history->changedBy->name }} • {{ $history->created_at->diffForHumans() }}
                                        </p>
                                        @if($history->reason)
                                            <p class="text-xs text-gray-600 mt-1"><strong>Reason:</strong> {{ $history->reason }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Courier Info -->
                @if($order->courier)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Assigned Courier</h3>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="bg-red-100 rounded-full p-3">
                                <i class="fas fa-user text-red-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $order->courier->name }}</p>
                                <p class="text-xs text-gray-500">{{ $order->courier->email }}</p>
                            </div>
                        </div>
                        @if($order->isChatActive())
                            <a href="{{ route('orders.chat', $order) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-bold transition text-center">
                                <i class="fas fa-comments mr-2"></i>Open Chat
                            </a>
                        @endif
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                        <i class="fas fa-hourglass-half text-yellow-600 text-2xl mb-2"></i>
                        <h3 class="font-bold text-yellow-900 mb-1">Awaiting Courier</h3>
                        <p class="text-sm text-yellow-800">Your order is visible to all couriers and will be accepted soon.</p>
                    </div>
                @endif

                <!-- Timeline -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Timeline</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Order Created</p>
                                <p class="text-xs text-gray-500">{{ $order->created_at->format('M d, h:i A') }}</p>
                            </div>
                        </div>
                        @if($order->accepted_at)
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Accepted</p>
                                    <p class="text-xs text-gray-500">{{ $order->accepted_at->format('M d, h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        @if($order->picked_up_at)
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Picked Up</p>
                                    <p class="text-xs text-gray-500">{{ $order->picked_up_at->format('M d, h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        @if($order->delivered_at)
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Delivered</p>
                                    <p class="text-xs text-gray-500">{{ $order->delivered_at->format('M d, h:i A') }}</p>
                                </div>
                            </div>
                        @endif
                        @if($order->cancelled_at)
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-times-circle text-red-500"></i>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">Cancelled</p>
                                    <p class="text-xs text-gray-500">{{ $order->cancelled_at->format('M d, h:i A') }}</p>
                                    @if($order->cancellation_reason)
                                        <p class="text-xs text-red-600 mt-1">{{ $order->cancellation_reason }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
