<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastTrack Courier - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-700 to-red-900 min-h-screen">
    <div class="container mx-auto px-4">
        <div class="flex flex-col lg:flex-row items-center justify-between min-h-screen py-12">
            <!-- Left side - Text content -->
            <div class="lg:w-1/2 text-white space-y-8">
                <h1 class="text-5xl lg:text-6xl font-bold leading-tight drop-shadow-lg">
                    FastTrack Courier Services
                </h1>
                <p class="text-xl lg:text-2xl text-red-100">
                    Your trusted partner for fast, reliable, and secure delivery solutions across the globe.
                </p>
                
                <ul class="space-y-3 text-lg">
                    <li class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        24/7 Express Delivery Services
                    </li>
                    <li class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Real-Time Package Tracking
                    </li>
                    <li class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Global Shipping Coverage
                    </li>
                    <li class="flex items-center">
                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Secure & Insured Deliveries
                    </li>
                </ul>
                
                @auth
                    <div class="bg-white/10 backdrop-blur-md rounded-lg p-6 border border-white/20">
                        <p class="text-lg mb-4">Welcome back, <strong class="text-yellow-300">{{ auth()->user()->name }}</strong>!</p>
                        <div class="flex flex-wrap gap-3">
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="bg-white text-red-700 px-6 py-3 rounded-full font-semibold hover:bg-red-50 transition">
                                    Admin Dashboard
                                </a>
                            @elseif(auth()->user()->role === 'courier')
                                <a href="{{ route('courier.dashboard') }}" class="bg-white text-red-700 px-6 py-3 rounded-full font-semibold hover:bg-red-50 transition">
                                    Courier Dashboard
                                </a>
                            @else
                                <a href="{{ route('user.dashboard') }}" class="bg-white text-red-700 px-6 py-3 rounded-full font-semibold hover:bg-red-50 transition">
                                    My Dashboard
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="border-2 border-white text-white px-6 py-3 rounded-full font-semibold hover:bg-white/10 transition">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex flex-wrap gap-4 pt-4">
                        <a href="{{ route('login') }}" class="bg-white text-red-700 px-8 py-4 rounded-full text-lg font-semibold hover:bg-red-50 transition shadow-lg">
                            Login to Dashboard
                        </a>
                        <a href="{{ route('register') }}" class="border-2 border-white text-white px-8 py-4 rounded-full text-lg font-semibold hover:bg-white/10 transition">
                            Create Account
                        </a>
                    </div>
                @endauth
                
                <p class="text-red-200 text-sm pt-4">
                    Trusted by 10,000+ businesses worldwide
                </p>
            </div>
            
            <!-- Right side - Image -->
            <div class="lg:w-1/2 mt-12 lg:mt-0 flex justify-center">
                <div class="max-w-md">
                    <img src="{{ asset('images/courier.svg') }}" alt="Courier Delivery Service" class="w-full drop-shadow-2xl">
                    <p class="text-white text-center mt-6 text-lg italic">
                        "Delivering happiness, one package at a time"
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
