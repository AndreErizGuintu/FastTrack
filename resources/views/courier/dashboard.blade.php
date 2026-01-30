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
                    <div class="h-8 w-px bg-red-400/30"></div>
                    <button onclick="openLogoutModal()" class="bg-white text-red-700 hover:bg-red-50 px-5 py-2 rounded-full font-bold transition shadow-md text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
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

        <div class="mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-gray-500">Here's an overview of your delivery operations for today.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-2xl p-4">
                        <i class="fas fa-box text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-6">
                        <p class="text-gray-500 text-sm font-bold uppercase tracking-wider">Pending Deliveries</p>
                        <p class="text-3xl font-black text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-100 rounded-2xl p-4">
                        <i class="fas fa-check-double text-emerald-600 text-2xl"></i>
                    </div>
                    <div class="ml-6">
                        <p class="text-gray-500 text-sm font-bold uppercase tracking-wider">Completed Today</p>
                        <p class="text-3xl font-black text-gray-900">0</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-md transition">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-2xl p-4">
                        <i class="fas fa-truck-moving text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-6">
                        <p class="text-gray-500 text-sm font-bold uppercase tracking-wider">In Transit</p>
                        <p class="text-3xl font-black text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gray-50/50 px-8 py-5 border-b border-gray-100">
                <h2 class="text-xl font-bold text-gray-800">Quick Actions</h2>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="group flex items-center p-6 border-2 border-gray-50 rounded-2xl hover:border-red-100 hover:bg-red-50/30 transition cursor-pointer">
                        <div class="bg-red-600 text-white rounded-xl p-4 mr-6 shadow-lg shadow-red-200 group-hover:scale-110 transition duration-300">
                            <i class="fas fa-clipboard-list text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-lg">View Assignments</p>
                            <p class="text-sm text-gray-500">Route details and customer information</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-gray-300 group-hover:text-red-500 transition"></i>
                    </div>

                    <div class="group flex items-center p-6 border-2 border-gray-50 rounded-2xl hover:border-emerald-100 hover:bg-emerald-50/30 transition cursor-pointer">
                        <div class="bg-emerald-600 text-white rounded-xl p-4 mr-6 shadow-lg shadow-emerald-200 group-hover:scale-110 transition duration-300">
                            <i class="fas fa-sync-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-lg">Update Status</p>
                            <p class="text-sm text-gray-500">Mark as picked up, delayed, or delivered</p>
                        </div>
                        <i class="fas fa-chevron-right ml-auto text-gray-300 group-hover:text-emerald-500 transition"></i>
                    </div>
                </div>
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
    </script>
</body>
</html>
