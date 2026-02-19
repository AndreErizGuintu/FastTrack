<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | FastTrack Courier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
@php
    // Get courier stats
    $activeOrder = \App\Models\DeliveryOrder::forCourier(auth()->id())->active()->first();
    $availableOrders = \App\Models\DeliveryOrder::where('status', 'awaiting_courier')->whereNull('courier_id')->count();
@endphp

<body class="bg-gray-50 min-h-screen font-sans">
    <nav class="bg-gradient-to-r from-red-700 to-red-900 text-white shadow-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                        <i class="fas fa-shipping-fast text-xl"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">FastTrack <span class="text-red-300">Courier</span></span>
                </div>
                
                <div class="flex items-center space-x-6">
                    <a href="{{ route('courier.dashboard') }}" class="hover:text-red-200 font-medium transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
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
                                        <p class="text-xs font-semibold text-emerald-800">{{ $availableOrders }} available orders</p>
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

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @if ($message = Session::get('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-4 py-4 rounded-r-lg mb-8 shadow-sm flex items-center">
                <i class="fas fa-check-circle mr-3 text-emerald-500"></i>
                <span class="font-medium">{{ $message }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-4 rounded-r-lg mb-8 shadow-sm">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                    <span class="font-bold">Please fix the following errors:</span>
                </div>
                <ul class="list-disc list-inside ml-8 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">My Profile</h1>
            <p class="text-gray-500">Manage your courier account information and preferences</p>
        </div>

        <!-- Profile Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="bg-gray-50/50 px-8 py-5 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Profile Information</h2>
            </div>
            <form action="{{ route('courier.profile.update') }}" method="POST" class="p-8">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email Address *</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold transition shadow-md">
                            <i class="fas fa-save mr-2"></i>Update Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gray-50/50 px-8 py-5 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Change Password</h2>
            </div>
            <form action="{{ route('courier.profile.password') }}" method="POST" class="p-8">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Current Password *</label>
                        <input type="password" name="current_password" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">New Password *</label>
                        <input type="password" name="password" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" required>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold transition shadow-md">
                            <i class="fas fa-key mr-2"></i>Update Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <footer class="mt-20 py-10 text-center text-gray-400 text-sm border-t border-gray-100">
        &copy; 2026 FastTrack Logistics Solutions.
    </footer>

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
