@extends('auth.layout')

@section('title', 'Register')

@section('content')
<!-- Header -->
<div class="bg-gradient-to-r from-red-700 to-red-800 px-8 py-6 text-center">
    <h2 class="text-2xl font-bold text-white mb-1">Join FastTrack</h2>
    <p class="text-red-100 text-sm">Create your account and get started today</p>
</div>

<!-- Body -->
<div class="px-8 py-8 max-h-[600px] overflow-y-auto">
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800 font-semibold flex items-center mb-2">
                <i class="fas fa-exclamation-circle mr-2"></i> Errors Found
            </p>
            <ul class="text-red-700 text-sm space-y-1 ml-6 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST" class="space-y-5">
        @csrf
        
        <!-- Full Name Field -->
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user mr-2 text-red-600"></i>Full Name
            </label>
            <input type="text" name="name" id="name" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent @error('name') border-red-500 @enderror" 
                   value="{{ old('name') }}" placeholder="John Doe" required autofocus>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-envelope mr-2 text-red-600"></i>Email Address
            </label>
            <input type="email" name="email" id="email" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent @error('email') border-red-500 @enderror" 
                   value="{{ old('email') }}" placeholder="you@example.com" required>
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Role Selection -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-briefcase mr-2 text-red-600"></i>Account Type
            </label>
            <div class="grid grid-cols-2 gap-3">
                <!-- User Option -->
                <div class="relative">
                    <input type="radio" id="role_user" name="role" value="user" class="hidden peer" @if(old('role') !== 'courier') checked @endif>
                    <label for="role_user" class="block p-4 border-2 border-gray-300 rounded-lg cursor-pointer text-center transition peer-checked:border-red-600 peer-checked:bg-red-50">
                        <i class="fas fa-user text-2xl text-gray-600 mb-2 block peer-checked:text-red-600"></i>
                        <p class="font-semibold text-gray-700 text-sm">User</p>
                        <p class="text-xs text-gray-500 mt-1">Track shipments</p>
                    </label>
                </div>
                <!-- Courier Option -->
                <div class="relative">
                    <input type="radio" id="role_courier" name="role" value="courier" class="hidden peer" @if(old('role') === 'courier') checked @endif>
                    <label for="role_courier" class="block p-4 border-2 border-gray-300 rounded-lg cursor-pointer text-center transition peer-checked:border-red-600 peer-checked:bg-red-50">
                        <i class="fas fa-truck text-2xl text-gray-600 mb-2 block peer-checked:text-red-600"></i>
                        <p class="font-semibold text-gray-700 text-sm">Courier</p>
                        <p class="text-xs text-gray-500 mt-1">Deliver orders</p>
                    </label>
                </div>
            </div>
            @error('role')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-lock mr-2 text-red-600"></i>Password
            </label>
            <input type="password" name="password" id="password" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent @error('password') border-red-500 @enderror" 
                   placeholder="••••••••" required>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters required</p>
        </div>

        <!-- Confirm Password Field -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-lock mr-2 text-red-600"></i>Confirm Password
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent" 
                   placeholder="••••••••" required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition duration-200 flex items-center justify-center">
            <i class="fas fa-user-check mr-2"></i>Create Account
        </button>
    </form>

    <!-- Divider -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Already registered?</span>
        </div>
    </div>

    <!-- Login Link -->
    <a href="{{ route('login') }}" class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 rounded-lg transition duration-200">
        <i class="fas fa-sign-in-alt mr-2"></i>Back to Login
    </a>
</div>
@endsection
