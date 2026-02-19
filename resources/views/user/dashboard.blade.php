<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastTrack | My Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes slideInDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes slideOutUp {
            from { transform: translateY(0); opacity: 1; }
            to { transform: translateY(-100%); opacity: 0; }
        }
        .toast-enter { animation: slideInDown 0.3s ease-out; }
        .toast-exit { animation: slideOutUp 0.3s ease-out; }
    </style>
</head>
@php
    // Get unread notifications for accepted, picked_up, arriving_at_dropoff, and cancelled statuses
    $unreadNotifications = \App\Models\OrderStatusHistory::whereNull('seen_at')
        ->whereIn('new_status', ['accepted', 'picked_up', 'arriving_at_dropoff', 'cancelled_by_user', 'cancelled_by_courier', 'cancelled_by_system'])
        ->whereHas('deliveryOrder', function($query) {
            $query->where('user_id', auth()->id());
        })
        ->with(['deliveryOrder', 'changedBy'])
        ->latest()
        ->get()
        ->unique(function($item) {
            return $item->delivery_order_id . '-' . $item->old_status . '-' . $item->new_status;
        });
    
    $unreadCount = $unreadNotifications->count();
@endphp

<body class="bg-gray-50 min-h-screen font-sans" @if(Session::has('recent_order_id')) data-recent-order-id="{{ Session::get('recent_order_id') }}" @endif>
    <!-- Success Toast -->
    <div id="successToast" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-600 text-white rounded-lg shadow-lg px-6 py-4 flex items-center space-x-3 z-50 max-w-md">
        <i class="fas fa-check-circle text-xl"></i>
        <div>
            <p class="font-bold text-sm" id="toastTitle">Success!</p>
            <p class="text-xs text-green-100" id="toastMessage">Action completed successfully.</p>
        </div>
        <button onclick="dismissToast()" class="ml-4 text-white hover:text-green-100 transition">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <nav class="bg-gradient-to-r from-red-700 to-red-900 text-white shadow-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-box-open text-xl"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">FastTrack <span class="text-red-300">User</span></span>
                </div>
                
                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="{{ route('user.dashboard') }}" class="hover:text-red-200 font-medium transition">My Dashboard</a>
                    </div>
                    
                    <!-- Notification Bell -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="relative hover:text-red-200 transition p-2">
                            <i class="fas fa-bell text-lg"></i>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 bg-yellow-400 text-red-900 text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $unreadCount }}</span>
                            @endif
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center rounded-t-lg">
                                <h3 class="font-bold text-gray-900 text-sm">Notifications</h3>
                                @if($unreadCount > 0)
                                    <button onclick="markAllAsRead()" class="text-xs text-red-600 hover:text-red-700 font-semibold">
                                        Mark all read
                                    </button>
                                @endif
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @if($unreadNotifications->isEmpty())
                                    <div class="p-6 text-center text-gray-500">
                                        <i class="fas fa-bell-slash text-3xl mb-2 text-gray-300"></i>
                                        <p class="text-sm">No new notifications</p>
                                    </div>
                                @else
                                    @foreach($unreadNotifications as $notification)
                                        @php
                                            $statusIcons = [
                                                'accepted' => ['icon' => 'fa-check-circle', 'color' => 'blue'],
                                                'picked_up' => ['icon' => 'fa-box', 'color' => 'purple'],
                                                'arriving_at_dropoff' => ['icon' => 'fa-location-arrow', 'color' => 'orange'],
                                                'cancelled_by_user' => ['icon' => 'fa-times-circle', 'color' => 'red'],
                                                'cancelled_by_courier' => ['icon' => 'fa-times-circle', 'color' => 'red'],
                                                'cancelled_by_system' => ['icon' => 'fa-times-circle', 'color' => 'red'],
                                            ];
                                            $statusConfig = $statusIcons[$notification->new_status] ?? ['icon' => 'fa-info-circle', 'color' => 'gray'];
                                            $statusLabel = str_replace('_', ' ', ucfirst($notification->new_status));
                                        @endphp
                                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition" onclick="viewNotification({{ $notification->id }}, {{ $notification->delivery_order_id }})">
                                            <div class="flex items-start space-x-3">
                                                <div class="bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-600 rounded-full p-2 mt-1">
                                                    <i class="fas {{ $statusConfig['icon'] }} text-sm"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-semibold text-gray-900">Order #{{ $notification->delivery_order_id }} - {{ $statusLabel }}</p>
                                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($notification->deliveryOrder->product_description, 50) }}</p>
                                                    <p class="text-xs text-gray-400 mt-1">
                                                        <i class="fas fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
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
                        <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 py-2">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('user.profile') }}" class="flex items-center space-x-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition">
                                <i class="fas fa-user w-5 text-gray-400"></i>
                                <span class="text-sm font-medium">My Profile</span>
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
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Hello, {{ auth()->user()->name }}!</h1>
            <p class="text-gray-500">Manage your shipments and account preferences below.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div class="bg-gray-50/50 px-8 py-5 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">Account Details</h2>
                <button class="text-red-600 hover:text-red-700 text-sm font-bold uppercase tracking-wider transition">Edit Profile</button>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Full Name</span>
                        <span class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Email Address</span>
                        <span class="text-lg font-semibold text-gray-900">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Account Status</span>
                        <div>
                            <span class="px-4 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold uppercase tracking-tighter">Premium User</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="mb-10">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Your Deliveries</h2>
                    <p class="text-gray-500 text-sm mt-1">Create and manage your delivery orders</p>
                </div>
                <button onclick="openCreateOrderModal()" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md flex items-center">
                    <i class="fas fa-plus mr-2"></i>Create Order
                </button>
            </div>

            @php
                $userOrders = \App\Models\DeliveryOrder::forUser(auth()->id())->latest()->take(10)->get();
                $statusColors = [
                    'draft' => 'gray',
                    'awaiting_courier' => 'yellow',
                    'courier_assigned' => 'yellow',
                    'accepted' => 'blue',
                    'arriving_at_pickup' => 'indigo',
                    'at_pickup' => 'indigo',
                    'picked_up' => 'purple',
                    'in_transit' => 'purple',
                    'arriving_at_dropoff' => 'orange',
                    'at_dropoff' => 'orange',
                    'delivered' => 'emerald',
                    'delivery_failed' => 'red',
                    'returned' => 'amber',
                    'cancelled_by_user' => 'red',
                    'cancelled_by_courier' => 'red',
                    'cancelled_by_system' => 'red',
                    'expired' => 'gray',
                ];
            @endphp

            @if($userOrders->isEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <i class="fas fa-box-open text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No orders yet</p>
                    <p class="text-gray-400 text-sm mb-6">Create your first delivery order to get started</p>
                    <button onclick="openCreateOrderModal()" class="inline-block bg-red-600 hover:bg-red-700 text-white px-8 py-2 rounded-lg font-bold transition">
                        <i class="fas fa-plus mr-2"></i>Create Order
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($userOrders as $order)
                        @php
                            $color = $statusColors[$order->status] ?? 'gray';
                        @endphp
                        <div class="bg-white rounded-lg border border-gray-100 p-4 hover:shadow-md hover:border-red-200 transition cursor-pointer group" onclick="openOrderModal({{ $order->id }})">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900">Order #{{ $order->id }} - {{ Str::limit($order->product_description, 40) }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-clock mr-1"></i>{{ $order->created_at->diffForHumans() }}
                                                @if($order->courier)
                                                    <i class="fas fa-user mx-2"></i>{{ $order->courier->name }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-2 text-xs text-gray-600">
                                        <i class="fas fa-location-dot text-red-600"></i>
                                        <span>{{ Str::limit($order->pickup_address, 30) }}</span>
                                        <i class="fas fa-arrow-right text-gray-400 mx-1"></i>
                                        <i class="fas fa-location-dot text-blue-600"></i>
                                        <span>{{ Str::limit($order->delivery_address, 30) }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="bg-{{ $color }}-100 text-{{ $color }}-800 px-3 py-1 rounded-full text-xs font-bold uppercase">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                    @if($order->courier_id && $order->isChatActive())
                                        <button onclick="openChatWidget({{ $order->id }}); event.stopPropagation();" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-xs font-bold flex items-center transition">
                                            <i class="fas fa-comments mr-1"></i>Chat
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-xl hover:border-red-100 transition duration-300">
                <div class="flex items-center mb-6">
                    <div class="bg-red-600 text-white rounded-xl p-4 shadow-lg shadow-red-100 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-history text-2xl"></i>
                    </div>
                    <h3 class="ml-5 text-xl font-bold text-gray-900">Order History</h3>
                </div>
                <p class="text-gray-600 leading-relaxed mb-4">View your complete order history, including past deliveries and tracking information.</p>
            </div>

            <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-xl hover:border-emerald-100 transition duration-300">
                <div class="flex items-center mb-6">
                    <div class="bg-emerald-600 text-white rounded-xl p-4 shadow-lg shadow-emerald-100 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-user-cog text-2xl"></i>
                    </div>
                    <h3 class="ml-5 text-xl font-bold text-gray-900">Profile Settings</h3>
                </div>
                <p class="text-gray-600 leading-relaxed mb-4">Update your delivery address, change password, and manage notification alerts.</p>
            </div>
        </div>
    </main>

    <footer class="mt-20 py-10 text-center text-gray-400 text-sm border-t border-gray-100">
        &copy; 2026 FastTrack Logistics Solutions.
    </footer>

    <!-- Create Order Modal -->
    <div id="createOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto p-4">
  <div class="bg-white rounded-xl shadow-lg max-w-6xl w-full my-8 max-h-[90vh] overflow-auto">
    <div class="bg-gradient-to-r from-red-700 to-red-900 text-white px-8 py-5 border-b border-gray-200">
      <h3 class="text-2xl font-bold flex items-center">
        <i class="fas fa-plus-circle mr-3"></i>Create New Delivery Order
      </h3>
      <p class="text-red-200 text-sm mt-1">Fill in the details for your delivery request</p>
    </div>

    <form action="{{ route('user.orders.store') }}" method="POST" class="p-8">
      @csrf
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Left 2 columns: All form inputs -->
        <div class="lg:col-span-2 space-y-5">
          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Product Description *</label>
            <textarea name="product_description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="What needs to be delivered?" required></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Weight (kg) *</label>
              <input type="number" step="0.1" name="estimated_weight" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="e.g., 2.5" required>
            </div>
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">Fee ($)</label>
              <div class="relative">
                <input type="number" step="0.01" id="delivery_fee" name="delivery_fee" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Auto-calculated" readonly>
                <div id="feeLoader" class="hidden absolute right-3 top-3">
                  <i class="fas fa-spinner fa-spin text-gray-400"></i>
                </div>
              </div>
              <button type="button" onclick="calculateFee()" class="mt-2 text-xs text-blue-600 hover:text-blue-800 font-semibold">
                <i class="fas fa-calculator mr-1"></i>Calculate Fee
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">Special Notes</label>
            <textarea name="special_notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Any special handling instructions..."></textarea>
          </div>

          <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg mb-6">
            <h4 class="font-bold text-emerald-900 text-sm mb-3 flex items-center">
              <i class="fas fa-box-open mr-2"></i>Pickup Details
            </h4>
            <div class="space-y-3">
              <div class="relative">
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Pickup Address *</label>
                <input type="text" id="pickup_address" name="pickup_address" oninput="searchAddress(this, 'pickup')" onblur="handleAddressBlur('pickup')" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Start typing address..." required autocomplete="off">
                <div id="pickup_suggestions" class="hidden absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto"></div>
                <p id="pickup_validation" class="text-xs mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Pickup Phone *</label>
                <div class="flex gap-2">
                  <select id="pickup_country_code" class="border border-gray-300 rounded-lg px-2 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm w-28">
                    <option value="">Loading...</option>
                  </select>
                  <input id="pickup_phone_number" type="tel" name="pickup_contact_phone" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="912 345 6789" required>
                </div>
                <p id="pickup_phone_hint" class="text-xs mt-1 text-gray-500"></p>
              </div>
            </div>
          </div>

          <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
            <h4 class="font-bold text-blue-900 text-sm mb-3 flex items-center">
              <i class="fas fa-map-marker-alt mr-2"></i>Delivery Details
            </h4>
            <div class="space-y-3">
              <div class="relative">
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Delivery Address *</label>
                <input type="text" id="delivery_address" name="delivery_address" oninput="searchAddress(this, 'delivery')" onblur="handleAddressBlur('delivery')" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Start typing address..." required autocomplete="off">
                <div id="delivery_suggestions" class="hidden absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto"></div>
                <p id="delivery_validation" class="text-xs mt-1 hidden"></p>
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Delivery Phone *</label>
                <input id="delivery_contact_phone" type="tel" name="delivery_contact_phone" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="e.g. +63 998 765 4321" required>
                <p id="delivery_phone_country" class="text-xs mt-1 text-gray-500">Type number with country code (example: +63)</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right column: Map -->
        <div id="locationMapContainer" class="bg-gray-50 rounded-lg border border-gray-200 p-5 sticky top-8 h-[420px]">
          <h4 class="font-bold text-gray-900 flex items-center mb-3">
            <i class="fas fa-map-marked-alt text-red-600 mr-2"></i>Pinpoint Exact Locations
          </h4>
          <p class="text-xs text-gray-600 mb-3">Drag markers to adjust exact pickup/delivery spots</p>
          <div id="locationMap" style="height: 350px; border-radius: 0.5rem; border: 2px solid #e5e7eb;"></div>
          <div class="grid grid-cols-2 gap-4 mt-3 text-xs">
            <div class="flex items-center space-x-2">
              <span class="w-4 h-4 bg-green-500 rounded-full border-2 border-white shadow"></span>
              <span class="text-gray-700"><strong>Green:</strong> Pickup Location (draggable)</span>
            </div>
            <div class="flex items-center space-x-2">
              <span class="w-4 h-4 bg-red-500 rounded-full border-2 border-white shadow"></span>
              <span class="text-gray-700"><strong>Red:</strong> Delivery Location (draggable)</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Hidden inputs for coordinates -->
      <input type="hidden" id="pickup_lat" name="pickup_lat">
      <input type="hidden" id="pickup_lng" name="pickup_lng">
      <input type="hidden" id="delivery_lat" name="delivery_lat">
      <input type="hidden" id="delivery_lng" name="delivery_lng">

      <div class="flex gap-3 pt-6 border-t border-gray-200">
        <button type="button" onclick="closeCreateOrderModal()" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-bold transition">
          Cancel
        </button>
        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold transition shadow-md">
          <i class="fas fa-check mr-2"></i>Create Order
        </button>
      </div>
    </form>
  </div>
</div>


    <!-- Order Details Modal -->
    <div id="orderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div id="orderModalContent" class="bg-white rounded-xl shadow-lg max-w-6xl w-full mx-4 my-8 max-h-[90vh] overflow-y-auto"></div>
    </div>

    <!-- Chat Widget (Bottom Right) -->
    <div id="chatWidget" class="hidden fixed bottom-4 right-4 w-96 bg-white rounded-lg shadow-2xl border border-gray-200 z-40 flex flex-col" style="max-height: 600px;">
        <div class="bg-gradient-to-r from-red-700 to-red-900 text-white p-4 rounded-t-lg flex justify-between items-center cursor-pointer" onclick="toggleChat()">
            <div>
                <p class="font-bold text-sm" id="chatCourierName">Chat with Courier</p>
                <p class="text-xs text-red-200" id="chatOrderId">Order #</p>
            </div>
            <button onclick="closeChatWidget(); event.stopPropagation();" class="text-white hover:text-red-200 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50" style="max-height: 400px;">
            <!-- Messages loaded here -->
        </div>

        <form id="chatForm" onsubmit="sendMessage(event)" class="border-t border-gray-200 p-4 bg-white rounded-b-lg">
            <div class="flex space-x-2">
                <input type="text" id="messageInput" placeholder="Type message..." class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-full transition">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Chat Minimized Button -->
    <div id="chatMinimized" class="hidden fixed bottom-4 right-4 bg-red-600 text-white rounded-full p-4 shadow-lg cursor-pointer z-40 hover:bg-red-700 transition" onclick="toggleChat()">
        <i class="fas fa-comments text-xl"></i>
        <span id="unreadCount" class="hidden absolute -top-2 -right-2 bg-yellow-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">0</span>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm mx-4">
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

    <!-- Leaflet CSS for map -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <script>
        let currentChatOrderId = null;
        let chatOpen = false;
        let locationMap = null;
        let pickupMarker = null;
        let deliveryMarker = null;
        let pickupCoords = null;
        let deliveryCoords = null;

        function openCreateOrderModal() {
            document.getElementById('createOrderModal').classList.remove('hidden');
            
            // Initialize map after modal opens
            setTimeout(() => {
                if (!locationMap) {
                    locationMap = L.map('locationMap').setView([14.5995, 120.9842], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(locationMap);
                } else {
                    // Force map to recalculate size if already initialized
                    locationMap.invalidateSize();
                }
            }, 200);
        }

        function closeCreateOrderModal() {
            document.getElementById('createOrderModal').classList.add('hidden');
            document.getElementById('locationMapContainer').classList.add('hidden');
            
            // Reset markers
            if (pickupMarker) {
                locationMap.removeLayer(pickupMarker);
                pickupMarker = null;
            }
            if (deliveryMarker) {
                locationMap.removeLayer(deliveryMarker);
                deliveryMarker = null;
            }
            pickupCoords = null;
            deliveryCoords = null;
        }

        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.getElementById('profileDropdown')?.classList.add('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        function openOrderModal(orderId) {
            fetch(`/user/orders/${orderId}`)
                .then(res => res.text())
                .then(html => {
                    const content = document.getElementById('orderModalContent');
                    content.innerHTML = html;
                    document.getElementById('orderModal').classList.remove('hidden');
                });
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }

        function openChatWidget(orderId) {
            currentChatOrderId = orderId;
            document.getElementById('chatWidget').classList.remove('hidden');
            document.getElementById('chatMinimized').classList.add('hidden');
            chatOpen = true;
            loadMessages();
        }

        function closeChatWidget() {
            document.getElementById('chatWidget').classList.add('hidden');
            document.getElementById('chatMinimized').classList.remove('hidden');
            chatOpen = false;
        }

        function toggleChat() {
            if (chatOpen) {
                closeChatWidget();
            } else {
                if (currentChatOrderId) {
                    document.getElementById('chatWidget').classList.remove('hidden');
                    chatOpen = true;
                }
            }
        }

        function loadMessages() {
            if (!currentChatOrderId) return;
            
            fetch(`/orders/${currentChatOrderId}/messages`)
                .then(res => res.text())
                .then(html => {
                    // Extract just the messages part
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const messagesDiv = doc.getElementById('messagesContainer');
                    if (messagesDiv) {
                        document.getElementById('messagesContainer').innerHTML = messagesDiv.innerHTML;
                        // Scroll to bottom
                        const container = document.getElementById('messagesContainer');
                        container.scrollTop = container.scrollHeight;
                    }
                })
                .catch(err => console.error('Failed to load messages:', err));
        }

        function sendMessage(e) {
            e.preventDefault();
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message || !currentChatOrderId) return;

            const formData = new FormData();
            formData.append('message', message);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(`/orders/${currentChatOrderId}/messages`, {
                method: 'POST',
                body: formData
            })
            .then(() => {
                input.value = '';
                loadMessages();
            })
            .catch(err => console.error('Failed to send message:', err));
        }

        // Auto-refresh messages every 5 seconds
        setInterval(() => {
            if (chatOpen && currentChatOrderId) {
                loadMessages();
            }
        }, 5000);

        // Close modals on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCreateOrderModal();
                closeOrderModal();
            }
        });

        // Close on background click
        document.getElementById('createOrderModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeCreateOrderModal();
        });

        document.getElementById('orderModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeOrderModal();
        });

        // Notification functions
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        }

        function viewNotification(notificationId, orderId) {
            // Mark notification as seen
            fetch(`/notifications/${notificationId}/mark-seen`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                // Open order modal
                openOrderModal(orderId);
                // Reload page to update notification count
                setTimeout(() => location.reload(), 100);
            });
        }

        function markAllAsRead() {
            fetch('/notifications/mark-all-seen', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => {
                location.reload();
            });
        }

        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationButton = e.target.closest('button[onclick="toggleNotifications()"]');
            if (!notificationDropdown?.contains(e.target) && !notificationButton) {
                notificationDropdown?.classList.add('hidden');
            }
            
            const profileDropdown = document.getElementById('profileDropdown');
            const profileButton = e.target.closest('button[onclick="toggleProfileMenu()"]');
            if (!profileDropdown?.contains(e.target) && !profileButton) {
                profileDropdown?.classList.add('hidden');
            }
        });

        // Profile dropdown toggle
        function toggleProfileMenu() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Address validation and autocomplete
        let validatedAddresses = { pickup: false, delivery: false };
        let searchTimeout = null;
        let phoneDialCodeIndex = [];

        async function loadPhoneDialCodes() {
            try {
                const response = await fetch('https://restcountries.com/v3.1/all?fields=name,idd,flag');
                const countries = await response.json();

                const dialCodes = [];
                countries.forEach(country => {
                    if (!country?.idd?.root) return;
                    const root = country.idd.root;
                    const suffixes = Array.isArray(country.idd.suffixes) && country.idd.suffixes.length
                        ? country.idd.suffixes
                        : [''];

                    suffixes.forEach(suffix => {
                        const code = `${root}${suffix}`;
                        if (/^\+\d+$/.test(code)) {
                            dialCodes.push({
                                code,
                                flag: country.flag || '🏳️',
                                name: country.name?.common || 'Unknown country'
                            });
                        }
                    });
                });

                phoneDialCodeIndex = dialCodes
                    .sort((a, b) => b.code.length - a.code.length)
                    .filter((item, index, arr) => arr.findIndex(x => x.code === item.code) === index);
            } catch (error) {
                phoneDialCodeIndex = [];
            }
        }

        function detectCountryByPhone(rawValue) {
            if (!rawValue) return null;

            let cleaned = rawValue.replace(/\s+/g, '');
            if (!cleaned.startsWith('+')) {
                cleaned = `+${cleaned.replace(/[^\d]/g, '')}`;
            }

            return phoneDialCodeIndex.find(item => cleaned.startsWith(item.code)) || null;
        }

        function updatePhoneCountryHint(inputId, hintId) {
            const input = document.getElementById(inputId);
            const hint = document.getElementById(hintId);
            if (!input || !hint) return;

            const match = detectCountryByPhone(input.value.trim());

            if (match) {
                hint.className = 'text-xs mt-1 text-green-700 font-medium';
                hint.textContent = `${match.flag} ${match.name} (${match.code})`;
                input.classList.remove('border-red-300');
                input.classList.add('border-green-300');
            } else {
                hint.className = 'text-xs mt-1 text-gray-500';
                hint.textContent = 'Type number with country code (example: +63)';
                input.classList.remove('border-green-300');
            }
        }

        async function searchAddress(input, type) {
            const query = input.value.trim();
            const suggestionsDiv = document.getElementById(`${type}_suggestions`);
            
            if (searchTimeout) clearTimeout(searchTimeout);

            if (query.length < 3) {
                suggestionsDiv.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=5&addressdetails=1`);
                    const results = await response.json();

                    if (results && results.length > 0) {
                        suggestionsDiv.innerHTML = results.map(result => {
                            const displayName = result.display_name.replace(/'/g, "&#39;");
                            return `
                                <div class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-0" onclick="selectAddress('${type}', '${displayName}', ${result.lat}, ${result.lon})">
                                    <div class="flex items-start">
                                        <i class="fas fa-map-marker-alt text-gray-400 mt-1 mr-2"></i>
                                        <div class="flex-1">
                                            <p class="text-sm text-gray-900 font-medium">${result.display_name}</p>
                                            <p class="text-xs text-gray-500 mt-0.5">${result.type || 'Location'}</p>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                        suggestionsDiv.classList.remove('hidden');
                    } else {
                        suggestionsDiv.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500 italic"><i class="fas fa-info-circle mr-2"></i>No addresses found. Try different keywords.</div>';
                        suggestionsDiv.classList.remove('hidden');
                    }
                } catch (error) {
                    console.error('Address search failed:', error);
                    suggestionsDiv.classList.add('hidden');
                }
            }, 300);
        }

        function selectAddress(type, address, lat, lng) {
            const input = document.getElementById(`${type}_address`);
            const suggestionsDiv = document.getElementById(`${type}_suggestions`);
            const validationMsg = document.getElementById(`${type}_validation`);

            input.value = address.replace(/&#39;/g, "'");
            suggestionsDiv.classList.add('hidden');

            validationMsg.classList.remove('hidden');
            validationMsg.className = 'text-xs mt-1 text-green-600';
            validationMsg.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Address verified';
            input.classList.remove('border-red-300');
            input.classList.add('border-green-300');
            validatedAddresses[type] = true;

            // Add marker to map
            addMarkerToMap(type, lat, lng);

            // Store coordinates
            if (type === 'pickup') {
                pickupCoords = { lat, lng };
                document.getElementById('pickup_lat').value = lat;
                document.getElementById('pickup_lng').value = lng;
            } else {
                deliveryCoords = { lat, lng };
                document.getElementById('delivery_lat').value = lat;
                document.getElementById('delivery_lng').value = lng;
            }

            // Show map container
            document.getElementById('locationMapContainer').classList.remove('hidden');
            
            // Force map to recalculate size after container becomes visible
            setTimeout(() => {
                if (locationMap) {
                    locationMap.invalidateSize();
                }
            }, 100);

            // Auto-calculate fee when both addresses are selected
            const pickupVal = document.getElementById('pickup_address').value.trim();
            const deliveryVal = document.getElementById('delivery_address').value.trim();
            if (pickupVal && deliveryVal && validatedAddresses.pickup && validatedAddresses.delivery) {
                calculateFeeWithCoords();
            }
        }

        function addMarkerToMap(type, lat, lng) {
            if (!locationMap) return;

            const markerColor = type === 'pickup' ? '#10b981' : '#ef4444';
            const markerIcon = L.divIcon({
                html: `<div style="width: 30px; height: 30px; background: ${markerColor}; border: 3px solid white; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>`,
                iconSize: [30, 30],
                className: ''
            });

            // Remove existing marker
            if (type === 'pickup' && pickupMarker) {
                locationMap.removeLayer(pickupMarker);
            } else if (type === 'delivery' && deliveryMarker) {
                locationMap.removeLayer(deliveryMarker);
            }

            // Add new draggable marker
            const marker = L.marker([lat, lng], {
                icon: markerIcon,
                draggable: true
            }).addTo(locationMap);

            marker.bindPopup(`<strong>${type === 'pickup' ? '🟢 Pickup' : '🔴 Delivery'} Location</strong><br><small>Drag to adjust position</small>`).openPopup();

            // Update coordinates on drag
            marker.on('dragend', function(e) {
                const newPos = e.target.getLatLng();
                if (type === 'pickup') {
                    pickupCoords = { lat: newPos.lat, lng: newPos.lng };
                    document.getElementById('pickup_lat').value = newPos.lat;
                    document.getElementById('pickup_lng').value = newPos.lng;
                } else {
                    deliveryCoords = { lat: newPos.lat, lng: newPos.lng };
                    document.getElementById('delivery_lat').value = newPos.lat;
                    document.getElementById('delivery_lng').value = newPos.lng;
                }

                // Recalculate fee with new coordinates
                if (pickupCoords && deliveryCoords) {
                    calculateFeeWithCoords();
                }
            });

            // Store marker reference
            if (type === 'pickup') {
                pickupMarker = marker;
            } else {
                deliveryMarker = marker;
            }

            // Fit bounds if both markers exist
            setTimeout(() => {
                locationMap.invalidateSize();
                if (pickupMarker && deliveryMarker) {
                    const bounds = L.latLngBounds([pickupMarker.getLatLng(), deliveryMarker.getLatLng()]);
                    locationMap.fitBounds(bounds, { padding: [50, 50] });
                } else {
                    locationMap.setView([lat, lng], 15);
                }
            }, 100);
        }

        function handleAddressBlur(type) {
            setTimeout(() => {
                document.getElementById(`${type}_suggestions`).classList.add('hidden');
            }, 200);
        }

        async function validateAddress(input, type) {
            const address = input.value.trim();
            if (!address) return;

            const validationMsg = document.getElementById(`${type}_validation`);
            validationMsg.classList.remove('hidden');
            validationMsg.className = 'text-xs mt-1 text-gray-500';
            validationMsg.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Validating...';

            try {
                const response = await fetch('/api/validate-address', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ address })
                });

                const data = await response.json();

                if (data.valid) {
                    validationMsg.className = 'text-xs mt-1 text-green-600';
                    validationMsg.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Address verified';
                    input.classList.remove('border-red-300');
                    input.classList.add('border-green-300');
                    validatedAddresses[type] = true;
                } else {
                    validationMsg.className = 'text-xs mt-1 text-red-600';
                    validationMsg.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i>Address not found';
                    input.classList.remove('border-green-300');
                    input.classList.add('border-red-300');
                    validatedAddresses[type] = false;
                }
            } catch (error) {
                validationMsg.className = 'text-xs mt-1 text-yellow-600';
                validationMsg.innerHTML = '<i class="fas fa-exclamation-triangle mr-1"></i>Could not validate';
                validatedAddresses[type] = false;
            }
        }

        // Fee calculation with precise coordinates from map
        async function calculateFeeWithCoords() {
            if (!pickupCoords || !deliveryCoords) return;

            const feeInput = document.getElementById('delivery_fee');
            const loader = document.getElementById('feeLoader');

            loader.classList.remove('hidden');
            feeInput.value = '';

            try {
                // Use OSRM directly with coordinates
                const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${pickupCoords.lng},${pickupCoords.lat};${deliveryCoords.lng},${deliveryCoords.lat}?overview=false`);
                const data = await response.json();

                if (data.routes && data.routes[0]) {
                    const distanceMeters = data.routes[0].distance;
                    const distanceKm = distanceMeters / 1000;
                    const fee = 5.00 + (distanceKm * 0.50);
                    
                    feeInput.value = fee.toFixed(2);
                    feeInput.classList.add('text-green-700', 'font-bold');
                } else {
                    feeInput.value = '';
                    alert('Could not calculate route. Please adjust marker positions.');
                }
            } catch (error) {
                console.error('Fee calculation error:', error);
                alert('Error calculating fee. Please try again.');
            } finally {
                loader.classList.add('hidden');
            }
        }

        // Fee calculation (fallback with addresses)
        async function calculateFee() {
            // If we have precise coordinates from map, use those
            if (pickupCoords && deliveryCoords) {
                calculateFeeWithCoords();
                return;
            }
            const pickupAddress = document.getElementById('pickup_address').value.trim();
            const deliveryAddress = document.getElementById('delivery_address').value.trim();
            const feeInput = document.getElementById('delivery_fee');
            const loader = document.getElementById('feeLoader');

            if (!pickupAddress || !deliveryAddress) {
                alert('Please enter both pickup and delivery addresses first.');
                return;
            }

            loader.classList.remove('hidden');
            feeInput.value = '';

            try {
                const response = await fetch('/api/calculate-fee', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ pickup_address: pickupAddress, delivery_address: deliveryAddress })
                });

                const data = await response.json();

                if (data.success) {
                    feeInput.value = data.fee;
                    feeInput.classList.add('text-green-700', 'font-bold');
                } else {
                    alert(data.message || 'Could not calculate fee. Please check addresses.');
                }
            } catch (error) {
                alert('Error calculating fee. Please try again.');
            } finally {
                loader.classList.add('hidden');
            }
        }

        // Phone country detection (flag + code)
        loadPhoneDialCodes().then(() => {
            updatePhoneCountryHint('pickup_contact_phone', 'pickup_phone_country');
            updatePhoneCountryHint('delivery_contact_phone', 'delivery_phone_country');
        });

        document.getElementById('pickup_contact_phone')?.addEventListener('input', function() {
            updatePhoneCountryHint('pickup_contact_phone', 'pickup_phone_country');
        });

        document.getElementById('delivery_contact_phone')?.addEventListener('input', function() {
            updatePhoneCountryHint('delivery_contact_phone', 'delivery_phone_country');
        });
    </script>
</body>
</html>
