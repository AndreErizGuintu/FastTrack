@extends('admin.layout')

@section('title', 'Edit User: ' . $user->name)

@section('content')

<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-8">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Name Field -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" 
                       value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror" 
                       value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Field -->
            <div class="mb-6">
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">User Role</label>
                <select name="role" id="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror" required>
                    <option value="">Select a role</option>
                    <option value="user" @if(old('role', $user->role) === 'user') selected @endif>
                        <i class="fas fa-user"></i> User
                    </option>
                    <option value="admin" @if(old('role', $user->role) === 'admin') selected @endif>
                        <i class="fas fa-crown"></i> Admin
                    </option>
                    <option value="courier" @if(old('role', $user->role) === 'courier') selected @endif>
                        <i class="fas fa-truck"></i> Courier
                    </option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Role Info -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-gray-700">
                    <strong>Current Role:</strong>
                    @if($user->role === 'admin')
                        <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold ml-2">
                            <i class="fas fa-crown mr-1"></i> Admin
                        </span>
                    @elseif($user->role === 'courier')
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs font-semibold ml-2">
                            <i class="fas fa-truck mr-1"></i> Courier
                        </span>
                    @else
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold ml-2">
                            <i class="fas fa-user mr-1"></i> User
                        </span>
                    @endif
                </p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition flex items-center">
                    <i class="fas fa-save mr-2"></i> Update User
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 transition flex items-center">
                    <i class="fas fa-times mr-2"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
