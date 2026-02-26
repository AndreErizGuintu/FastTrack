@if($messages->isEmpty())
    <div class="text-center py-8 text-gray-500">
        <i class="fas fa-comments text-4xl text-gray-300 mb-2"></i>
        <p class="text-sm font-medium">No messages yet</p>
        <p class="text-xs text-gray-400">Start the conversation below</p>
    </div>
@else
    <div class="space-y-3">
        @foreach($messages as $message)
            @php
                $isMyMessage = $message->sender_id === auth()->id();
            @endphp
            <div class="flex {{ $isMyMessage ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%]">
                    <p class="text-xs mb-1 {{ $isMyMessage ? 'text-right text-red-600' : 'text-gray-500' }}">
                        {{ $message->sender->name }} • {{ $message->created_at->diffForHumans() }}
                    </p>
                    <div class="{{ $isMyMessage ? 'bg-red-600 text-white' : 'bg-white border border-gray-200 text-gray-900' }} rounded-xl px-3 py-2 shadow-sm">
                        <p class="text-sm">{{ $message->message }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
