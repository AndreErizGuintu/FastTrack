<div class="bg-white rounded-xl shadow-lg max-w-4xl w-full mx-auto">
    <div class="bg-gradient-to-r from-red-700 to-red-900 text-white px-6 py-4 rounded-t-xl flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Order #{{ $order->id }}</h2>
            <p class="text-xs text-red-100 mt-1">Tracking ID: {{ $order->tracking_id ?? 'N/A' }}</p>
        </div>
        <button onclick="closeOrderModal()" class="text-white hover:text-red-200 transition">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <div class="p-6 space-y-5">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Status</span>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 uppercase">{{ str_replace('_', ' ', $order->status) }}</span>
            <span class="text-xs text-gray-500">Created {{ $order->created_at->diffForHumans() }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs uppercase text-gray-500 font-bold mb-1">Pickup</p>
                <p class="text-sm text-gray-900">{{ $order->pickup_address }}</p>
                <p class="text-xs text-gray-600 mt-2"><i class="fas fa-phone mr-1"></i>{{ $order->pickup_contact_phone }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs uppercase text-gray-500 font-bold mb-1">Dropoff</p>
                <p class="text-sm text-gray-900">{{ $order->delivery_address }}</p>
                <p class="text-xs text-gray-600 mt-2"><i class="fas fa-phone mr-1"></i>{{ $order->delivery_contact_phone }}</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-xs uppercase text-gray-500 font-bold mb-1">Item</p>
            <p class="text-sm text-gray-900">{{ $order->product_description }}</p>
            @if($order->delivery_fee)
                <p class="text-sm text-emerald-700 font-semibold mt-2">Estimated Fee: ${{ number_format($order->delivery_fee, 2) }}</p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 pt-2">
            <a href="{{ route('user.orders.show', $order) }}" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition">
                <i class="fas fa-up-right-from-square mr-2"></i>Open Full Details
            </a>
            @if($order->courier_id && $order->isChatActive())
                <button onclick="openChatWidget({{ $order->id }}); closeOrderModal();" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition">
                    <i class="fas fa-comments mr-2"></i>Open Chat
                </button>
            @endif
        </div>
    </div>
</div>
