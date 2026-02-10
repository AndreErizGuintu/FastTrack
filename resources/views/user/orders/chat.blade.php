<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Chat | FastTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <nav class="bg-gradient-to-r from-red-700 to-red-900 text-white shadow-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <a href="{{ auth()->user()->role === 'courier' ? route('courier.dashboard') : route('user.orders.index') }}" class="hover:bg-white/10 px-3 py-2 rounded-lg transition">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div>
                        <p class="text-sm text-red-200">Order #{{ $order->id }}</p>
                        <p class="font-bold">
                            @if(auth()->user()->role === 'courier')
                                {{ $order->user->name }}
                            @else
                                {{ $order->courier ? $order->courier->name : 'No Courier Assigned' }}
                            @endif
                        </p>
                    </div>
                </div>
                
                <span class="bg-white/20 px-4 py-2 rounded-full text-sm font-bold">
                    {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
            </div>
        </div>
    </nav>

    @if(!$order->isChatActive())
        <div class="bg-yellow-50 border-b border-yellow-200 px-4 py-3">
            <div class="max-w-4xl mx-auto flex items-center">
                <i class="fas fa-info-circle text-yellow-600 mr-3"></i>
                <p class="text-sm text-yellow-800 font-medium">This chat is now read-only. The order has been {{ $order->status }}.</p>
            </div>
        </div>
    @endif

    @if ($message = Session::get('success'))
        <div class="bg-emerald-50 border-b border-emerald-200 px-4 py-3">
            <div class="max-w-4xl mx-auto flex items-center">
                <i class="fas fa-check-circle text-emerald-600 mr-3"></i>
                <p class="text-sm text-emerald-800 font-medium">{{ $message }}</p>
            </div>
        </div>
    @endif

    <main class="flex-1 max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 flex flex-col">
        <!-- Messages Container -->
        <div id="messagesContainer" class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4 overflow-y-auto" style="max-height: calc(100vh - 300px);">
            @if($messages->isEmpty())
                <div class="text-center py-12">
                    <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No messages yet</p>
                    <p class="text-gray-400 text-sm">Start the conversation below</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($messages as $message)
                        @php
                            $isMyMessage = $message->sender_id === auth()->id();
                        @endphp
                        <div class="flex {{ $isMyMessage ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-md">
                                <div class="flex items-center space-x-2 mb-1 {{ $isMyMessage ? 'justify-end' : '' }}">
                                    <p class="text-xs font-bold {{ $isMyMessage ? 'text-red-600' : 'text-gray-600' }}">
                                        {{ $message->sender->name }}
                                        @if($message->sender->role === 'courier')
                                            <i class="fas fa-shipping-fast ml-1"></i>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="{{ $isMyMessage ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-900' }} rounded-2xl px-4 py-3">
                                    <p class="text-sm">{{ $message->message }}</p>
                                </div>
                                @if($message->is_read && $isMyMessage)
                                    <p class="text-xs text-gray-400 text-right mt-1">
                                        <i class="fas fa-check-double"></i> Read
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Message Input -->
        @if($order->isChatActive())
            <form action="{{ route('orders.messages.store', $order) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                @csrf
                <div class="flex space-x-3">
                    <input type="text" name="message" placeholder="Type your message..." class="flex-1 border border-gray-300 rounded-full px-6 py-3 focus:ring-2 focus:ring-red-500 focus:border-transparent" required autofocus>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-full font-bold transition shadow-md">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        @else
            <div class="bg-gray-100 rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-gray-600 font-medium">
                    <i class="fas fa-lock mr-2"></i>Chat is closed for this order
                </p>
            </div>
        @endif
    </main>

    <script>
        // Auto-scroll to bottom on page load
        const container = document.getElementById('messagesContainer');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        // Optional: Auto-refresh for new messages (simple polling)
        // setInterval(() => location.reload(), 10000); // Refresh every 10 seconds
    </script>
</body>
</html>
