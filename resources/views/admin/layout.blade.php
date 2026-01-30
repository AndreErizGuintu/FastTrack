<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-red-900 to-red-950 text-white flex flex-col shadow-lg">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-red-800">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-lock mr-3 text-red-400"></i>Admin Panel
                </h1>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('admin.dashboard')) bg-red-600 @else hover:bg-red-800 @endif transition">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Products Section -->
                <div class="mt-6">
                    <p class="px-4 py-2 text-xs font-semibold text-red-300 uppercase tracking-wider">Products</p>
                    <a href="{{ route('products.index') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('products.index', 'products.show', 'products.edit', 'products.create')) bg-red-600 @else hover:bg-red-800 @endif transition">
                        <i class="fas fa-boxes mr-3"></i>
                        <span>All Products</span>
                    </a>
                    <a href="{{ route('products.create') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('products.create')) bg-red-600 @else hover:bg-red-800 @endif transition">
                        <i class="fas fa-plus mr-3"></i>
                        <span>Add Product</span>
                    </a>
                </div>

                <!-- Users Section -->
                <div class="mt-6">
                    <p class="px-4 py-2 text-xs font-semibold text-red-300 uppercase tracking-wider">Users</p>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('admin.users.index', 'admin.users.edit')) bg-red-600 @else hover:bg-red-800 @endif transition">
                        <i class="fas fa-users mr-3"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-red-800 space-y-2">
                <div class="text-sm text-red-200 px-4 py-2">
                    <p class="font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs">{{ auth()->user()->email }}</p>
                </div>
                <button onclick="openLogoutModal()" class="w-full flex items-center px-4 py-3 rounded-lg hover:bg-red-600 transition text-left">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation -->
            <div class="bg-white shadow-sm p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-3xl font-bold text-gray-900">@yield('title')</h2>
                </div>
            </div>

            <!-- Page Content -->
            <div class="p-8">
                @if ($message = Session::get('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ $message }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <strong>Validation Errors:</strong>
                        <ul class="mt-2 ml-4 list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
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

    <script>
        function openLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }
        document.getElementById('logoutModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeLogoutModal();
        });
    </script></body>
</html