<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | FastTrack</title>
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
                    <a href="{{ route('user.dashboard') }}" class="hover:text-red-200 font-medium transition">Dashboard</a>
                    <a href="{{ route('user.orders.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold transition shadow-md text-sm">
                        <i class="fas fa-plus mr-2"></i>New Order
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
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">My Delivery Orders</h1>
            <p class="text-gray-500">Track and manage all your delivery requests</p>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-16 text-center">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-700 mb-2">No Orders Yet</h3>
                <p class="text-gray-500 mb-6">You haven't created any delivery orders yet</p>
                <a href="{{ route('user.orders.create') }}" class="inline-block bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold transition shadow-md">
                    <i class="fas fa-plus mr-2"></i>Create Your First Order
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Order #{{ $order->id }}</p>
                                <p class="text-lg font-bold text-gray-900 mt-1">{{ Str::limit($order->product_description, 50) }}</p>
                                <p class="text-sm text-gray-500 mt-1">Created {{ $order->created_at->diffForHumans() }}</p>
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
                            <span class="bg-{{ $color }}-100 text-{{ $color }}-800 px-4 py-2 rounded-full text-xs font-bold uppercase">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            @if($order->courier)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold mb-1">Courier</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ $order->courier->name }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pickup</p>
                                <p class="text-sm text-gray-900">{{ Str::limit($order->pickup_address, 40) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Delivery</p>
                                <p class="text-sm text-gray-900">{{ Str::limit($order->delivery_address, 40) }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('user.orders.show', $order) }}" class="bg-gray-700 hover:bg-gray-800 text-white px-5 py-2 rounded-lg font-bold transition text-sm">
                                <i class="fas fa-eye mr-2"></i>View Details
                            </a>

                            @if($order->status === 'draft')
                                <form action="{{ route('user.orders.confirm', $order) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg font-bold transition text-sm">
                                        <i class="fas fa-check mr-2"></i>Confirm & Post
                                    </button>
                                </form>
                            @endif

                            @if($order->status !== 'draft' && $order->isChatActive())
                                <a href="{{ route('orders.chat', $order) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-bold transition text-sm">
                                    <i class="fas fa-comments mr-2"></i>Chat with Courier
                                </a>
                            @endif

                            @if(!$order->isCompleted())
                                <button onclick="openCancelModal({{ $order->id }})" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg font-bold transition text-sm">
                                    <i class="fas fa-times mr-2"></i>Cancel Order
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @endif
    </main>

    <!-- Cancel Order Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-4">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 rounded-full p-3">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Cancel Order</h3>
            <p class="text-gray-600 text-center mb-6">Please provide a reason for cancelling this order.</p>
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
        function openCancelModal(orderId) {
            const form = document.getElementById('cancelForm');
            form.action = `/user/orders/${orderId}/cancel`;
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
