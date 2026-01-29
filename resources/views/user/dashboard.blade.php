<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-indigo-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold">User Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('user.dashboard') }}" class="hover:bg-indigo-700 px-3 py-2 rounded">Dashboard</a>
                    <a href="{{ route('home') }}" class="hover:bg-indigo-700 px-3 py-2 rounded">Home</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if ($message = Session::get('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ $message }}
            </div>
        @endif

        <h1 class="text-3xl font-bold text-gray-800 mb-6">Welcome, {{ auth()->user()->name }}</h1>

        <!-- Info Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Account Information</h2>
            <div class="space-y-3">
                <div class="flex">
                    <span class="text-gray-600 w-32">Name:</span>
                    <span class="font-medium text-gray-900">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-600 w-32">Email:</span>
                    <span class="font-medium text-gray-900">{{ auth()->user()->email }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-600 w-32">Account Type:</span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">Regular User</span>
                </div>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-indigo-100 rounded-lg p-3">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-4 text-lg font-semibold text-gray-800">Track Orders</h3>
                </div>
                <p class="text-gray-600">View and track your order history and shipment status.</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 rounded-lg p-3">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="ml-4 text-lg font-semibold text-gray-800">Profile Settings</h3>
                </div>
                <p class="text-gray-600">Manage your account settings and preferences.</p>
            </div>
        </div>
    </div>
</body>
</html>
