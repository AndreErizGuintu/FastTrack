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
    <nav class="bg-gradient-to-r from-red-700 to-red-900 text-white shadow-xl sticky top-0 z-50">
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
                        <a href="{{ route('home') }}" class="hover:text-red-200 font-medium transition">Home</a>
                    </div>
                    <div class="h-8 w-px bg-red-400/30"></div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-white text-red-700 hover:bg-red-50 px-5 py-2 rounded-full font-bold transition shadow-md text-sm">
                            Logout
                        </button>
                    </form>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-xl hover:border-red-100 transition duration-300 cursor-pointer">
                <div class="flex items-center mb-6">
                    <div class="bg-red-600 text-white rounded-xl p-4 shadow-lg shadow-red-100 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-map-marker-alt text-2xl"></i>
                    </div>
                    <h3 class="ml-5 text-xl font-bold text-gray-900">Track Orders</h3>
                </div>
                <p class="text-gray-600 leading-relaxed mb-4">View your active shipments, estimated arrival times, and complete order history.</p>
                <span class="text-red-600 font-bold flex items-center group-hover:translate-x-2 transition duration-300">
                    View Tracking <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </span>
            </div>

            <div class="group bg-white rounded-2xl shadow-sm border border-gray-100 p-8 hover:shadow-xl hover:border-emerald-100 transition duration-300 cursor-pointer">
                <div class="flex items-center mb-6">
                    <div class="bg-emerald-600 text-white rounded-xl p-4 shadow-lg shadow-emerald-100 group-hover:scale-110 transition duration-300">
                        <i class="fas fa-user-cog text-2xl"></i>
                    </div>
                    <h3 class="ml-5 text-xl font-bold text-gray-900">Profile Settings</h3>
                </div>
                <p class="text-gray-600 leading-relaxed mb-4">Update your delivery address, change password, and manage notification alerts.</p>
                <span class="text-emerald-600 font-bold flex items-center group-hover:translate-x-2 transition duration-300">
                    Manage Account <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </span>
            </div>
        </div>
    </main>

    <footer class="mt-20 py-10 text-center text-gray-400 text-sm border-t border-gray-100">
        &copy; 2026 FastTrack Logistics Solutions.
    </footer>
</body>
</html>