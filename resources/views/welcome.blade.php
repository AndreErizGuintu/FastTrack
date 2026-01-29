<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastTrack Courier | Global Logistics Solutions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <nav class="fixed w-full z-50 bg-white/90 backdrop-blur-md shadow-sm">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="bg-red-600 p-2 rounded-lg">
                    <i class="fas fa-shipping-fast text-white text-xl"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-gray-800">Fast<span class="text-red-600">Track</span></span>
            </div>
            
            <div class="hidden md:flex space-x-8 font-medium">
                <a href="#services" class="hover:text-red-600 transition">Services</a>
                <a href="#tracking" class="hover:text-red-600 transition">Track Package</a>
                <a href="#about" class="hover:text-red-600 transition">About Us</a>
            </div>

            <div>
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-red-600 text-white px-5 py-2 rounded-full hover:bg-red-700 transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 px-4 py-2 hover:text-red-600 transition">Login</a>
                    <a href="{{ route('register') }}" class="bg-red-600 text-white px-5 py-2 rounded-full hover:bg-red-700 transition">Join Now</a>
                @endauth
            </div>
        </div>
    </nav>

    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-red-700 to-red-900 -skew-y-3 origin-top-left transform scale-110 -z-10"></div>
        
        <div class="container mx-auto px-6 flex flex-col lg:flex-row items-center">
            <div class="lg:w-1/2 text-white space-y-8">
                <span class="bg-red-500/30 text-red-100 px-4 py-1 rounded-full text-sm font-semibold tracking-wide uppercase">New: Drone Delivery in Select Cities</span>
                <h1 class="text-5xl lg:text-7xl font-extrabold leading-tight">
                    Speedy Delivery, <br><span class="text-red-300">Global Reach.</span>
                </h1>
                <p class="text-xl text-red-100 max-w-lg">
                    We don't just move boxes; we move businesses. Experience the next generation of logistics with real-time AI tracking.
                </p>
                
                <div class="flex flex-wrap gap-4 pt-4">
                    <div class="bg-white p-1 rounded-full flex items-center shadow-xl">
                        <input type="text" placeholder="Enter Tracking Number" class="pl-6 pr-2 py-3 rounded-full text-gray-800 outline-none w-48 lg:w-64">
                        <button class="bg-red-600 text-white px-6 py-3 rounded-full font-bold hover:bg-red-700 transition">
                            Track
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:w-1/2 mt-16 lg:mt-0 relative">
                <div class="relative z-10 animate-float">
                    <img src="images/fasttrack.png" alt="Delivery illustration" class="w-full drop-shadow-2xl">
                </div>
                <div class="absolute top-0 -right-10 w-64 h-64 bg-red-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
            </div>
        </div>
    </section>

    <section class="py-12 bg-white border-b">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <p class="text-4xl font-bold text-red-600">99.9%</p>
                    <p class="text-gray-600 uppercase tracking-widest text-sm font-semibold">On-Time Rate</p>
                </div>
                <div>
                    <p class="text-4xl font-bold text-red-600">24/7</p>
                    <p class="text-gray-600 uppercase tracking-widest text-sm font-semibold">Live Support</p>
                </div>
                <div>
                    <p class="text-4xl font-bold text-red-600">150+</p>
                    <p class="text-gray-600 uppercase tracking-widest text-sm font-semibold">Countries</p>
                </div>
                <div>
                    <p class="text-4xl font-bold text-red-600">1M+</p>
                    <p class="text-gray-600 uppercase tracking-widest text-sm font-semibold">Packages/Year</p>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="py-24">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">Why Choose FastTrack?</h2>
                <p class="text-gray-600">We offer a wide range of logistics services tailored to your personal or business needs.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                    <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center mb-6 group-hover:bg-red-600 transition">
                        <i class="fas fa-bolt text-red-600 text-2xl group-hover:text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Express Shipping</h3>
                    <p class="text-gray-600">Same-day delivery for local shipments and 48-hour global priority services.</p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                    <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center mb-6 group-hover:bg-red-600 transition">
                        <i class="fas fa-shield-alt text-red-600 text-2xl group-hover:text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Secure Logistics</h3>
                    <p class="text-gray-600">Full insurance coverage and tamper-proof packaging for your high-value items.</p>
                </div>

                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition group">
                    <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center mb-6 group-hover:bg-red-600 transition">
                        <i class="fas fa-warehouse text-red-600 text-2xl group-hover:text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Warehousing</h3>
                    <p class="text-gray-600">Smart storage solutions and inventory management for e-commerce businesses.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center space-x-2 text-white mb-6">
                        <i class="fas fa-shipping-fast text-red-500"></i>
                        <span class="text-2xl font-bold">FastTrack</span>
                    </div>
                    <p>Making logistics seamless and delivery effortless since 2010.</p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="hover:text-white">About Us</a></li>
                        <li><a href="#" class="hover:text-white">Careers</a></li>
                        <li><a href="#" class="hover:text-white">Sustainability</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Support</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">API Docs</a></li>
                        <li><a href="#" class="hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Newsletter</h4>
                    <div class="flex">
                        <input type="text" placeholder="Email" class="bg-gray-800 border-none rounded-l-lg px-4 py-2 w-full focus:ring-1 focus:ring-red-500">
                        <button class="bg-red-600 text-white px-4 py-2 rounded-r-lg hover:bg-red-700 transition">Go</button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                &copy; 2026 FastTrack Courier Services. All rights reserved.
            </div>
        </div>
    </footer>

    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</body>
</html>
