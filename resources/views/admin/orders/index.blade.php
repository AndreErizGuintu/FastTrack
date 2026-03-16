@extends('admin.layout')

@section('title', 'Manage Orders')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4">
            <p class="text-sm font-semibold text-gray-800 mb-2">Quick Status</p>
            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('admin.orders.index', array_merge(request()->except('status', 'page'), ['status' => ''])) }}"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ ($filters['status'] ?? '') === '' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}"
                >
                    All
                </a>
                @foreach($statuses as $status)
                    <a
                        href="{{ route('admin.orders.index', array_merge(request()->except('status', 'page'), ['status' => $status])) }}"
                        class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ ($filters['status'] ?? '') === $status ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}"
                    >
                        {{ str_replace('_', ' ', $status) }}
                    </a>
                @endforeach
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str_replace('_', ' ', $status) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Tracking ID</label>
                <input
                    type="text"
                    name="tracking_id"
                    value="{{ $filters['tracking_id'] ?? '' }}"
                    placeholder="FT-2026..."
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Proof of Delivery</label>
                <select name="proof" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="" @selected(($filters['proof'] ?? '') === '')>Any</option>
                    <option value="with" @selected(($filters['proof'] ?? '') === 'with')>With proof</option>
                    <option value="without" @selected(($filters['proof'] ?? '') === 'without')>Without proof</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">
                    Apply
                </button>
                <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-semibold">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Orders</h3>
            <p class="text-sm text-gray-500">{{ $orders->total() }} total</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Courier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Proof</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm">
                                <p class="font-semibold text-gray-900">#{{ $order->id }}</p>
                                <p class="text-xs text-gray-500">{{ $order->tracking_id ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->user->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $order->courier->name ?? 'Unassigned' }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 uppercase">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($order->pod_image_path)
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Yes</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">No</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-xs font-semibold">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 opacity-50"></i>
                                <p>No orders found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
