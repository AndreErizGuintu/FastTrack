@extends('admin.layout')

@section('title', 'Edit Product: ' . $product->name)

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Edit Product</h2>
    <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 transition flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
</div>

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
        <h3 class="text-red-800 font-bold mb-2">Validation Errors:</h3>
        <ul class="text-red-700 space-y-1">
            @foreach ($errors->all() as $error)
                <li class="flex items-start">
                    <i class="fas fa-times-circle mr-2 mt-1"></i>
                    <span>{{ $error }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-lg shadow p-8 max-w-2xl">
    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Product Name -->
        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Product Name</label>
            <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" 
                   placeholder="Enter product name" value="{{ $product->name }}" required>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Product Detail -->
        <div class="mb-6">
            <label for="detail" class="block text-sm font-medium text-gray-700 mb-2">Product Details</label>
            <textarea name="detail" id="detail" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('detail') border-red-500 @enderror" 
                      placeholder="Enter product details" required>{{ $product->detail }}</textarea>
            @error('detail')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Who (Creator) -->
        <div class="mb-6">
            <label for="who" class="block text-sm font-medium text-gray-700 mb-2">Creator / Owner</label>
            <input type="text" name="who" id="who" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('who') border-red-500 @enderror" 
                   placeholder="Enter creator or owner name" value="{{ $product->who }}" required>
            @error('who')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Warehouse -->
        <div class="mb-6">
            <label for="warehouse" class="block text-sm font-medium text-gray-700 mb-2">Warehouse Location</label>
            <input type="text" name="warehouse" id="warehouse" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('warehouse') border-red-500 @enderror" 
                   placeholder="Enter warehouse location" value="{{ $product->warehouse }}" required>
            @error('warehouse')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Courier Name -->
        <div class="mb-6">
            <label for="courier_name" class="block text-sm font-medium text-gray-700 mb-2">Courier Name</label>
            <input type="text" name="courier_name" id="courier_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('courier_name') border-red-500 @enderror" 
                   placeholder="Enter courier name" value="{{ $product->courier_name }}" required>
            @error('courier_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition flex items-center">
                <i class="fas fa-save mr-2"></i> Update Product
            </button>
            <a href="{{ route('products.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 transition flex items-center">
                <i class="fas fa-times mr-2"></i> Cancel
            </a>
        </div>

    </form>
</div>

@endsection
