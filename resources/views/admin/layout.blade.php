<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white flex flex-col">
            <!-- Sidebar Header -->
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-2xl font-bold flex items-center">
                    <i class="fas fa-lock mr-3 text-blue-400"></i>Admin Panel
                </h1>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('admin.dashboard')) bg-blue-600 @else hover:bg-gray-700 @endif transition">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Products Section -->
                <div class="mt-6">
                    <p class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Products</p>
                    <a href="{{ route('products.index') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('products.index', 'products.show', 'products.edit', 'products.create')) bg-blue-600 @else hover:bg-gray-700 @endif transition">
                        <i class="fas fa-boxes mr-3"></i>
                        <span>All Products</span>
                    </a>
                    <a href="{{ route('products.create') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('products.create')) bg-blue-600 @else hover:bg-gray-700 @endif transition">
                        <i class="fas fa-plus mr-3"></i>
                        <span>Add Product</span>
                    </a>
                </div>

                <!-- Users Section -->
                <div class="mt-6">
                    <p class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Users</p>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 rounded-lg @if(request()->routeIs('admin.users.index', 'admin.users.edit')) bg-blue-600 @else hover:bg-gray-700 @endif transition">
                        <i class="fas fa-users mr-3"></i>
                        <span>Manage Users</span>
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-gray-700 space-y-2">
                <div class="text-sm text-gray-400 px-4 py-2">
                    <p class="font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="text-xs">{{ auth()->user()->email }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 rounded-lg hover:bg-red-600 transition text-left">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation -->
            <div class="bg-white shadow-md p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-3xl font-bold text-gray-800">@yield('title')</h2>
                    <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
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
</body>
</html>
