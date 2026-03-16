@extends('admin.layout')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Order #{{ $order->id }}</h3>
                <p class="text-sm text-gray-500">Tracking ID: {{ $order->tracking_id ?? 'N/A' }}</p>
                <p class="text-xs text-gray-500 mt-1">Created {{ $order->created_at->format('M d, Y h:i A') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 uppercase">
                {{ str_replace('_', ' ', $order->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-bold uppercase text-gray-500 mb-1">User</p>
                <p class="text-sm text-gray-900">{{ $order->user->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-600">{{ $order->user->email ?? 'N/A' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-bold uppercase text-gray-500 mb-1">Courier</p>
                <p class="text-sm text-gray-900">{{ $order->courier->name ?? 'Unassigned' }}</p>
                <p class="text-xs text-gray-600">{{ $order->courier->email ?? 'N/A' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-bold uppercase text-gray-500 mb-1">Pickup</p>
                <p class="text-sm text-gray-900">{{ $order->pickup_address }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $order->pickup_contact_phone }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-bold uppercase text-gray-500 mb-1">Dropoff</p>
                <p class="text-sm text-gray-900">{{ $order->delivery_address }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $order->delivery_contact_phone }}</p>
            </div>
        </div>

        <div class="mt-4 bg-gray-50 rounded-lg p-4">
            <p class="text-xs font-bold uppercase text-gray-500 mb-1">Item</p>
            <p class="text-sm text-gray-900">{{ $order->product_description }}</p>
            @if($order->delivery_fee)
                <p class="text-sm text-emerald-700 font-semibold mt-2">Delivery Fee: ${{ number_format($order->delivery_fee, 2) }}</p>
            @endif
        </div>

        <div class="mt-4">
            <p class="text-xs font-bold uppercase text-gray-500 mb-2">Proof of Delivery</p>
            @if($order->pod_image_path)
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Available</span>
            @else
                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Missing</span>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Status Override</h3>
        <form method="POST" action="{{ route('admin.orders.status.update', $order) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-gray-600 mb-1">New Status</label>
                <select name="status" class="w-full md:w-80 border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                    <option value="">Select status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(old('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Reason</label>
                <textarea name="reason" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" maxlength="500" required>{{ old('reason') }}</textarea>
            </div>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">
                Update Status
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status History</h3>
        @if($order->statusHistory->isEmpty())
            <p class="text-sm text-gray-500">No status history available.</p>
        @else
            <div class="space-y-3">
                @foreach($order->statusHistory->sortByDesc('created_at') as $history)
                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $history->old_status ?? 'new' }} → {{ $history->new_status }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            By {{ $history->changedBy->name ?? 'Unknown' }} ({{ $history->actor_type }}) • {{ $history->created_at->format('M d, Y h:i A') }}
                        </p>
                        @if($history->reason)
                            <p class="text-xs text-gray-600 mt-1">Reason: {{ $history->reason }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
