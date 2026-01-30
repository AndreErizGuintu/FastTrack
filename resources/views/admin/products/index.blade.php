@extends('admin.layout')

@section('title', 'Products Management')

@section('content')

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Products</h2>
    <a href="{{ route('products.create') }}" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition flex items-center">
        <i class="fas fa-plus mr-2"></i> New Product
    </a>
</div>

@if ($message = Session::get('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
        {{ $message }}
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Who</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Warehouse</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Courier</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm text-gray-500">{{ ++$i }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $product->detail }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $product->who }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $product->warehouse }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $product->courier_name }}</td>
                        <td class="px-6 py-3 text-sm text-right space-x-2">
                            <a href="{{ route('products.show', $product->id) }}" class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition text-xs font-medium">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                            <a href="{{ route('products.edit', $product->id) }}" class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition text-xs font-medium">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <button onclick="openDeleteModal('{{ route('products.destroy', $product->id) }}', '{{ $product->name }}')" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition text-xs font-medium">
                                <i class="fas fa-trash mr-1"></i> Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $products->links() }}
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm mx-4 animate-in">
        <div class="flex items-center justify-center mb-4">
            <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Delete Product</h3>
        <p class="text-gray-600 text-center mb-2">Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
        <p class="text-gray-500 text-center text-sm mb-6">This action cannot be undone.</p>
        <div class="flex gap-3">
            <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition">
                Cancel
            </button>
            <form id="deleteForm" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const deleteForm = document.getElementById('deleteForm');
    function openDeleteModal(action, itemName) {
        deleteForm.action = action;
        document.getElementById('deleteItemName').textContent = itemName;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }
    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>

@endsection
