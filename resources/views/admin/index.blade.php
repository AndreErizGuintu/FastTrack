@extends('admin.layout')

@section('title', 'Manage Users')

@section('content')

<div class="grid grid-cols-1 gap-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $users->total() }}</p>
                </div>
                <i class="fas fa-users text-4xl text-blue-500 opacity-20"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Admins</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $users->getCollection()->where('role', 'admin')->count() }}</p>
                </div>
                <i class="fas fa-crown text-4xl text-yellow-500 opacity-20"></i>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Couriers</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $users->getCollection()->where('role', 'courier')->count() }}</p>
                </div>
                <i class="fas fa-truck text-4xl text-orange-500 opacity-20"></i>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Users List</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $user->id }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($user->role === 'admin')
                                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold flex items-center w-fit">
                                        <i class="fas fa-crown mr-1 text-xs"></i> Admin
                                    </span>
                                @elseif($user->role === 'courier')
                                    <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-semibold flex items-center w-fit">
                                        <i class="fas fa-truck mr-1 text-xs"></i> Courier
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold flex items-center w-fit">
                                        <i class="fas fa-user mr-1 text-xs"></i> User
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-edit mr-1 text-xs"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 opacity-50"></i>
                                <p class="mt-2">No users found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>
</div>

@endsection
