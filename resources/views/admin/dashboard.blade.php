@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Total Products</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalProducts ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalUsers ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Couriers</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $totalCouriers ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('products.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="bg-blue-100 rounded-lg p-3 mr-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Add New Product</p>
                        <p class="text-sm text-gray-500">Create a new product entry</p>
                    </div>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="bg-green-100 rounded-lg p-3 mr-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Manage Users</p>
                        <p class="text-sm text-gray-500">View and edit user accounts</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Proof of Delivery</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-emerald-200 bg-emerald-50 rounded-lg p-4">
                    <p class="text-xs uppercase font-bold text-emerald-700 mb-1">Delivered with Proof</p>
                    <p class="text-2xl font-bold text-emerald-800">{{ $deliveredWithProof ?? 0 }}</p>
                </div>
                <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4">
                    <p class="text-xs uppercase font-bold text-yellow-700 mb-1">Delivered without Proof</p>
                    <p class="text-2xl font-bold text-yellow-800">{{ $deliveredWithoutProof ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mt-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Orders</h2>
            @if(($recentOrders ?? collect())->isEmpty())
                <p class="text-sm text-gray-500">No recent orders yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($recentOrders as $order)
                        <div class="border border-gray-200 rounded-lg p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Order #{{ $order->id }}</p>
                                    <p class="text-xs text-gray-500">Tracking ID: {{ $order->tracking_id ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        User: {{ $order->user->name ?? 'N/A' }}
                                        @if($order->courier)
                                            • Courier: {{ $order->courier->name }}
                                        @endif
                                    </p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 uppercase font-semibold">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

@endsection
