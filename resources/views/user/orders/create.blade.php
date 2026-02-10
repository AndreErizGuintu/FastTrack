<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Delivery Order | FastTrack</title>
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
                    <a href="{{ route('user.orders.index') }}" class="hover:text-red-200 font-medium transition">My Orders</a>
                    <div class="h-8 w-px bg-red-400/30"></div>
                    <a href="{{ route('user.dashboard') }}" class="bg-white text-red-700 hover:bg-red-50 px-5 py-2 rounded-full font-bold transition shadow-md text-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Create Delivery Order</h1>
            <p class="text-gray-500">Fill in the details below to request a delivery</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-4 rounded-r-lg mb-8 shadow-sm">
                <p class="font-bold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.orders.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @csrf
            
            <div class="p-8 space-y-6">
                <!-- Product Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-box text-red-600 mr-2"></i>Product Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="product_description" class="block text-sm font-bold text-gray-700 mb-2">Product Description *</label>
                            <textarea id="product_description" name="product_description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Describe the item to be delivered..." required>{{ old('product_description') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="estimated_weight" class="block text-sm font-bold text-gray-700 mb-2">Estimated Weight (kg) *</label>
                                <input type="number" step="0.1" id="estimated_weight" name="estimated_weight" value="{{ old('estimated_weight') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="e.g., 2.5" required>
                            </div>

                            <div>
                                <label for="delivery_fee" class="block text-sm font-bold text-gray-700 mb-2">Delivery Fee ($)</label>
                                <input type="number" step="0.01" id="delivery_fee" name="delivery_fee" value="{{ old('delivery_fee') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="e.g., 25.00">
                            </div>
                        </div>

                        <div>
                            <label for="special_notes" class="block text-sm font-bold text-gray-700 mb-2">Special Notes</label>
                            <textarea id="special_notes" name="special_notes" rows="2" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Any special handling instructions...">{{ old('special_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Pickup Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt text-red-600 mr-2"></i>Pickup Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="pickup_address" class="block text-sm font-bold text-gray-700 mb-2">Pickup Address *</label>
                            <input type="text" id="pickup_address" name="pickup_address" value="{{ old('pickup_address') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Full pickup address" required>
                        </div>

                        <div>
                            <label for="pickup_contact_phone" class="block text-sm font-bold text-gray-700 mb-2">Pickup Contact Phone *</label>
                            <input type="tel" id="pickup_contact_phone" name="pickup_contact_phone" value="{{ old('pickup_contact_phone') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="+1 (555) 123-4567" required>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="pb-2">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-location-dot text-red-600 mr-2"></i>Delivery Information
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="delivery_address" class="block text-sm font-bold text-gray-700 mb-2">Delivery Address *</label>
                            <input type="text" id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="Full delivery address" required>
                        </div>

                        <div>
                            <label for="delivery_contact_phone" class="block text-sm font-bold text-gray-700 mb-2">Delivery Contact Phone *</label>
                            <input type="tel" id="delivery_contact_phone" name="delivery_contact_phone" value="{{ old('delivery_contact_phone') }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" placeholder="+1 (555) 987-6543" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 flex justify-end space-x-4">
                <a href="{{ route('user.dashboard') }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-bold transition">
                    Cancel
                </a>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-bold transition shadow-md">
                    <i class="fas fa-paper-plane mr-2"></i>Create Order
                </button>
            </div>
        </form>
    </main>
</body>
</html>
