<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastTrack | Courier Dashboard</title>
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
                    <span class="text-xl font-bold tracking-tight">FastTrack <span class="text-red-300">Courier</span></span>
                </div>
                
                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="{{ route('courier.dashboard') }}" class="hover:text-red-200 font-medium transition">Dashboard</a>
                    </div>
                    
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
                        <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50 py-2">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                                @if($activeOrder)
                                    <div class="mt-2 bg-blue-50 rounded px-2 py-1.5">
                                        <p class="text-xs font-semibold text-blue-800">Active Order #{{ $activeOrder->id }}</p>
                                        <p class="text-xs text-blue-600">{{ ucfirst(str_replace('_', ' ', $activeOrder->status)) }}</p>
                                    </div>
                                @else
                                    <div class="mt-2 bg-emerald-50 rounded px-2 py-1.5">
                                        <p class="text-xs font-semibold text-emerald-800">{{ $availableOrders->count() }} available orders</p>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('courier.dashboard') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-home w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">Dashboard</span>
                            </a>
                            <a href="{{ route('courier.profile') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-user w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">My Profile</span>
                            </a>
                            <a href="#" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-cog w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">Settings</span>
                            </a>
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <button onclick="openLogoutModal()" class="w-full flex items-center space-x-3 px-4 py-2.5 text-red-600 hover:bg-red-50 transition">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span class="text-sm font-medium">Logout</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
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

        <div class="mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-gray-500">
                @if($activeOrder)
                    You have an active delivery in progress.
                @else
                    {{ $availableOrders->count() }} available orders waiting for pickup.
                @endif
            </p>
        </div>

        @if($activeOrder)
            <!-- Active Delivery Section -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl shadow-lg border-2 border-red-200 p-8 mb-10">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="bg-red-600 text-white rounded-xl p-3">
                            <i class="fas fa-shipping-fast text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Active Delivery</h2>
                            <p class="text-sm text-gray-600">Order #{{ $activeOrder->id }}</p>
                        </div>
                    </div>
                    <span class="bg-{{ $activeOrder->status === 'accepted' ? 'yellow' : 'blue' }}-600 text-white px-4 py-2 rounded-full text-sm font-bold uppercase">
                        {{ ucfirst(str_replace('_', ' ', $activeOrder->status)) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Customer</p>
                        <p class="text-lg font-bold text-gray-900">{{ $activeOrder->user->name }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Product</p>
                        <p class="text-lg font-bold text-gray-900">{{ Str::limit($activeOrder->product_description, 40) }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Pickup Address</p>
                        <p class="text-sm text-gray-900">{{ $activeOrder->pickup_address }}</p>
                        <p class="text-sm text-gray-600 mt-1"><i class="fas fa-phone mr-1"></i>{{ $activeOrder->pickup_contact_phone }}</p>
                    </div>
                    <div class="bg-white rounded-xl p-5 shadow-sm">
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Delivery Address</p>
                        <p class="text-sm text-gray-900">{{ $activeOrder->delivery_address }}</p>
                        <p class="text-sm text-gray-600 mt-1"><i class="fas fa-phone mr-1"></i>{{ $activeOrder->delivery_contact_phone }}</p>
                    </div>
                </div>

                @if($activeOrder->special_notes)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                        <p class="text-xs text-yellow-800 uppercase font-bold mb-1"><i class="fas fa-info-circle mr-1"></i>Special Notes</p>
                        <p class="text-sm text-yellow-900">{{ $activeOrder->special_notes }}</p>
                    </div>
                @endif

                <!-- Delivery Route Map -->
                @if($activeOrder->courierLocations->isNotEmpty())
                    <div class="bg-white rounded-xl p-5 shadow-sm mb-6">
                        <h3 class="text-md font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-route text-red-600 mr-2"></i>Your Delivery Route
                        </h3>
                        @include('components.order-map', ['order' => $activeOrder])
                    </div>
                @endif

                <div class="flex flex-wrap gap-3">
                    @if($activeOrder->status === 'accepted')
                        <form action="{{ route('courier.arriving_at_pickup', $activeOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-location-arrow mr-2"></i>Start Heading to Pickup
                            </button>
                        </form>
                    @elseif($activeOrder->status === 'arriving_at_pickup')
                        <form action="{{ route('courier.at_pickup', $activeOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-map-marker-alt mr-2"></i>I'm at Pickup Location
                            </button>
                        </form>
                    @elseif($activeOrder->status === 'at_pickup')
                        <form action="{{ route('courier.pickup', $activeOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-box mr-2"></i>Mark as Picked Up
                            </button>
                        </form>
                    @elseif($activeOrder->status === 'picked_up')
                        <form action="{{ route('courier.in_transit', $activeOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-truck-moving mr-2"></i>Start Transit
                            </button>
                        </form>
                    @elseif($activeOrder->status === 'in_transit')
                        <form action="{{ route('courier.arriving_at_dropoff', $activeOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-location-arrow mr-2"></i>Arriving at Dropoff
                            </button>
                        </form>
                    @elseif($activeOrder->status === 'arriving_at_dropoff')
                        <form action="{{ route('courier.at_dropoff', $activeOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-map-marker-alt mr-2"></i>I'm at Dropoff Location
                            </button>
                        </form>
                    @elseif($activeOrder->status === 'at_dropoff')
                        <form action="{{ route('courier.deliver', $activeOrder) }}" method="POST" class="inline mr-2">
                            @csrf
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-check-circle mr-2"></i>Mark as Delivered
                            </button>
                        </form>
                        <form action="{{ route('courier.delivery_failed', $activeOrder) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                                <i class="fas fa-times-circle mr-2"></i>Delivery Failed
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('orders.chat', $activeOrder) }}" class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                        <i class="fas fa-comments mr-2"></i>Chat with Customer
                    </a>
                    @if($activeOrder->isCancellableByCourier())
                        <button onclick="openCancelModal({{ $activeOrder->id }})" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
                            <i class="fas fa-times-circle mr-2"></i>Cancel Order
                        </button>
                    @endif
                </div>
            </div>
        @else
            <!-- Available Orders Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50/50 px-8 py-5 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800">Available Orders</h2>
                    <p class="text-sm text-gray-500 mt-1">Accept an order to start delivery</p>
                </div>
                <div class="p-8">
                    @if($availableOrders->isEmpty())
                        <div class="text-center py-12">
                            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500 text-lg">No orders available at the moment</p>
                            <p class="text-gray-400 text-sm">Check back soon for new delivery requests</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($availableOrders as $order)
                                <div class="border-2 border-gray-100 rounded-xl p-6 hover:border-red-200 hover:bg-red-50/30 transition">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold">Order #{{ $order->id }}</p>
                                            <p class="text-lg font-bold text-gray-900 mt-1">{{ $order->user->name }}</p>
                                        </div>
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold uppercase">Awaiting Courier</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Product</p>
                                            <p class="text-sm text-gray-900">{{ Str::limit($order->product_description, 50) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Weight</p>
                                            <p class="text-sm text-gray-900">{{ $order->estimated_weight }} kg</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pickup</p>
                                            <p class="text-sm text-gray-900">{{ Str::limit($order->pickup_address, 50) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Delivery</p>
                                            <p class="text-sm text-gray-900">{{ Str::limit($order->delivery_address, 50) }}</p>
                                        </div>
                                    </div>

                                    @if($order->delivery_fee)
                                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2 mb-4 inline-block">
                                            <p class="text-sm text-emerald-800 font-bold"><i class="fas fa-dollar-sign mr-1"></i>Fee: ${{ number_format($order->delivery_fee, 2) }}</p>
                                        </div>
                                    @endif

                                    <form action="{{ route('courier.accept', $order) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-bold transition shadow-md w-full md:w-auto">
                                            <i class="fas fa-hand-point-right mr-2"></i>Accept Order
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Order History Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-10">
            <div class="bg-gray-50/50 px-8 py-5 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Delivery History</h2>
                <p class="text-sm text-gray-500 mt-1">Delivered and cancelled orders</p>
            </div>
            <div class="p-8">
                @if($orderHistory->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-history text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No completed deliveries yet</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($orderHistory as $order)
                            @php
                                $historyColor = $order->status === 'delivered' ? 'emerald' : 'red';
                            @endphp
                            <div class="border-2 border-gray-100 rounded-xl p-5 hover:border-{{ $historyColor }}-200 transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold">Order #{{ $order->id }}</p>
                                        <p class="text-lg font-bold text-gray-900 mt-1">{{ $order->user->name }}</p>
                                        <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="bg-{{ $historyColor }}-100 text-{{ $historyColor }}-800 px-3 py-1 rounded-full text-xs font-bold uppercase">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pickup</p>
                                        <p class="text-sm text-gray-900">{{ Str::limit($order->pickup_address, 50) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase font-bold mb-1">Delivery</p>
                                        <p class="text-sm text-gray-900">{{ Str::limit($order->delivery_address, 50) }}</p>
                                    </div>
                                </div>
                                @if($order->cancellation_reason)
                                    <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 mt-4">
                                        <p class="text-xs text-red-700 font-bold uppercase">Cancellation Reason</p>
                                        <p class="text-sm text-red-800">{{ $order->cancellation_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm mx-4 animate-in">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Confirm Logout</h3>
            <p class="text-gray-600 text-center mb-6">Are you sure you want to log out of your account?</p>
            <div class="flex gap-3">
                <button onclick="closeLogoutModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                    Cancel
                </button>
                <form action="{{ route('logout') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-4">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Cancel Order</h3>
            <p class="text-gray-600 text-center mb-6">Please provide a reason for cancelling this delivery.</p>
            <form id="cancelForm" method="POST">
                @csrf
                <textarea name="cancellation_reason" class="w-full border border-gray-300 rounded-lg p-3 mb-4" rows="3" placeholder="Reason for cancellation..." required></textarea>
                <div class="flex gap-3">
                    <button type="button" onclick="closeCancelModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                        Close
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.getElementById('profileDropdown')?.classList.add('hidden');
        }
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }
        document.getElementById('logoutModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeLogoutModal();
        });

        function openCancelModal(orderId) {
            const form = document.getElementById('cancelForm');
            form.action = `/courier/orders/${orderId}/cancel`;
            document.getElementById('cancelModal').classList.remove('hidden');
        }
        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
        }
        document.getElementById('cancelModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCancelModal();
        });

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
