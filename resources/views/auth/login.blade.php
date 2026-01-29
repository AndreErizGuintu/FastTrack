@extends('auth.layout')

@section('title', 'Login')

@section('content')
<!-- Header -->
<div class="bg-gradient-to-r from-red-700 to-red-800 px-8 py-6 text-center">
    <h2 class="text-2xl font-bold text-white mb-1">Welcome Back</h2>
    <p class="text-red-100 text-sm">Sign in to your account</p>
</div>

<!-- Body -->
<div class="px-8 py-8">
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

    @if ($message = Session::get('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-2"></i>
            <p class="text-green-800">{{ $message }}</p>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="space-y-5">
        @csrf
        
        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-envelope mr-2 text-red-600"></i>Email Address
            </label>
            <input type="email" name="email" id="email" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent @error('email') border-red-500 @enderror" 
                   value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-red-600 rounded border-gray-300 focus:ring-red-500">
            <label for="remember" class="ml-2 text-sm text-gray-700">Remember me for 30 days</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-semibold py-3 rounded-lg transition duration-200 flex items-center justify-center">
            <i class="fas fa-sign-in-alt mr-2"></i>Sign In
        </button>
    </form>

    <!-- Divider -->
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">New to FastTrack?</span>
        </div>
    </div>

    <!-- Register Link -->
    <a href="{{ route('register') }}" class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 rounded-lg transition duration-200">
        <i class="fas fa-user-plus mr-2"></i>Create New Account
    </a>
</div>
@endsection
