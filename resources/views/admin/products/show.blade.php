@extends('admin.layout')

@section('title', $product->name)

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h2>
    <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-400 transition flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
</div>

<div class="bg-white rounded-lg shadow p-8 max-w-3xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Product Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name</label>
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-gray-900 font-medium">{{ $product->name }}</p>
            </div>
        </div>

        <!-- Creator / Owner -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Creator / Owner</label>
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-gray-900 font-medium">{{ $product->who }}</p>
            </div>
        </div>

        <!-- Warehouse -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Warehouse Location</label>
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-gray-900 font-medium">{{ $product->warehouse }}</p>
            </div>
        </div>

        <!-- Courier Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Courier Name</label>
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-gray-900 font-medium">{{ $product->courier_name }}</p>
            </div>
        </div>

    </div>

    <!-- Full Width Details -->
    <div class="mt-6">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Product Details</label>
        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg whitespace-pre-wrap">
            <p class="text-gray-900">{{ $product->detail }}</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-8 flex gap-3">
        <a href="{{ route('products.edit', $product->id) }}" class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition flex items-center">
            <i class="fas fa-edit mr-2"></i> Edit Product
        </a>
        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition flex items-center">
                <i class="fas fa-trash mr-2"></i> Delete Product
            </button>
        </form>
    </div>

</div>

@endsection
