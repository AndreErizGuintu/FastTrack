<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastTrack | My Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen font-sans">
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
                    <div class="h-8 w-px bg-red-400/30"></div>
                    <button onclick="openLogoutModal()" class="bg-white text-red-700 hover:bg-red-50 px-5 py-2 rounded-full font-bold transition shadow-md text-sm">
                        Logout
                    </button>
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
                    'pending' => 'yellow',
                    'accepted' => 'blue',
                    'in_transit' => 'purple',
                    'delivered' => 'emerald',
                    'cancelled' => 'red',
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
    <div id="createOrderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-lg max-w-2xl mx-4 my-8">
            <div class="bg-gradient-to-r from-red-700 to-red-900 text-white p-6 border-b border-gray-200">
                <h3 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-plus-circle mr-3"></i>Create New Delivery Order
                </h3>
                <p class="text-red-200 text-sm mt-1">Fill in the details for your delivery request</p>
            </div>
            
            <form action="{{ route('user.orders.store') }}" method="POST" class="p-8 space-y-5 max-h-96 overflow-y-auto">
                @csrf
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Product Description *</label>
                    <textarea name="product_description" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="What needs to be delivered?" required></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Weight (kg) *</label>
                        <input type="number" step="0.1" name="estimated_weight" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="e.g., 2.5" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Fee ($)</label>
                        <input type="number" step="0.01" name="delivery_fee" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="e.g., 25.00">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pickup Address *</label>
                    <input type="text" name="pickup_address" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Full address" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Pickup Phone *</label>
                    <input type="tel" name="pickup_contact_phone" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Delivery Address *</label>
                    <input type="text" name="delivery_address" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Full address" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Delivery Phone *</label>
                    <input type="tel" name="delivery_contact_phone" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Special Notes</label>
                    <textarea name="special_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm" placeholder="Any special handling instructions..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeCreateOrderModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-bold transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold transition">
                        Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
        <div id="orderModalContent" class="bg-white rounded-xl shadow-lg max-w-2xl mx-4 my-8"></div>
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

    <script>
        let currentChatOrderId = null;
        let chatOpen = false;

        function openCreateOrderModal() {
            document.getElementById('createOrderModal').classList.remove('hidden');
        }

        function closeCreateOrderModal() {
            document.getElementById('createOrderModal').classList.add('hidden');
        }

        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
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
    </script>
</body>
</html>
