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
                    <a href="{{ route('user.orders.index') }}" class="hover:text-red-200 font-medium transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                    </a>
                    
                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <button onclick="toggleProfileMenu()" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 px-3 py-2 rounded-full transition">
                            <div class="w-8 h-8 bg-white text-red-700 rounded-full flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden md:block font-medium">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <!-- Profile Dropdown Menu -->
                        <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 py-2">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('user.profile') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-user w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">My Profile</span>
                            </a>
                            <a href="{{ route('user.dashboard') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-home w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">Dashboard</span>
                            </a>
                            <a href="{{ route('user.orders.index') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-box w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">My Orders</span>
                            </a>
                            <a href="#" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-cog w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">Settings</span>
                            </a>
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-2.5 text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span class="text-sm font-medium">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if ($message = Session::get('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-4 py-4 rounded-r-lg mb-8 shadow-sm flex items-center">
                <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
                <span class="font-medium">{{ $message }}</span>
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-4 rounded-r-lg mb-8 shadow-sm flex items-center">
                <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                <span class="font-medium">{{ $message }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-4 rounded-r-lg mb-8 shadow-sm">
                <p class="font-bold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Order #{{ $order->id }}</h1>
                    <p class="text-gray-500">Created {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                </div>
                @php
                    $statusColors = [
                        'draft' => 'gray',
                        'awaiting_courier' => 'yellow',
                        'courier_assigned' => 'yellow',
                        'accepted' => 'blue',
                        'arriving_at_pickup' => 'purple',
                        'at_pickup' => 'purple',
                        'picked_up' => 'purple',
                        'in_transit' => 'purple',
                        'arriving_at_dropoff' => 'purple',
                        'at_dropoff' => 'purple',
                        'delivered' => 'emerald',
                        'delivery_failed' => 'orange',
                        'returned' => 'orange',
                        'cancelled_by_user' => 'red',
                        'cancelled_by_courier' => 'red',
                        'cancelled_by_system' => 'red',
                        'expired' => 'red',
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
                <!-- Product Information - READ ONLY -->
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
                                <p class="text-gray-900 font-semibold">{{ $order->estimated_weight }} kg</p>
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

                <!-- Location Details - READ ONLY -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-red-600 mr-2"></i>Location Details
                    </h2>
                    <div class="space-y-4">
                        <div class="border-l-4 border-emerald-500 pl-4 py-2">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pickup Address</p>
                            <p class="text-gray-900 mb-2 font-semibold">{{ $order->pickup_address }}</p>
                            <p class="text-sm text-gray-600"><i class="fas fa-phone mr-1"></i>{{ $order->pickup_contact_phone }}</p>
                        </div>
                        <div class="border-l-4 border-blue-500 pl-4 py-2">
                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Delivery Address</p>
                            <p class="text-gray-900 mb-2 font-semibold">{{ $order->delivery_address }}</p>
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
                            @foreach($order->statusHistory->unique(function($item) { return $item->old_status . '-' . $item->new_status . '-' . $item->created_at->format('YmdHis'); })->sortByDesc('created_at') as $history)
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
                        @if($order->status === 'draft')
                            <h3 class="font-bold text-yellow-900 mb-1">Not Yet Posted</h3>
                            <p class="text-sm text-yellow-800 mb-4">Confirm your order to make it visible to couriers.</p>
                            <div class="space-y-2">
                                <form action="{{ route('user.orders.confirm', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-lg font-bold transition">
                                        <i class="fas fa-check mr-2"></i>Confirm & Post
                                    </button>
                                </form>
                                <form action="{{ route('user.orders.cancel', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-lg font-bold transition">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                </form>
                            </div>
                        @else
                            <h3 class="font-bold text-yellow-900 mb-1">Awaiting Courier</h3>
                            <p class="text-sm text-yellow-800">Your order is visible to all couriers and will be accepted soon.</p>
                        @endif
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

                {{-- Order Again Button --}}
                @if($order->status === 'delivered')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <form action="{{ route('user.orders.reorder', $order) }}" method="POST" style="display: inline-block; width: 100%;">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                <i class="fas fa-redo mr-2"></i>Order Again
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <script>
        function toggleProfileMenu() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close profile dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const profileDropdown = document.getElementById('profileDropdown');
            const profileButton = e.target.closest('button[onclick="toggleProfileMenu()"]');
            if (!profileDropdown?.contains(e.target) && !profileButton) {
                profileDropdown?.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
