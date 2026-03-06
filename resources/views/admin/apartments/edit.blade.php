@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-4xl font-bold text-gray-900">Edit Apartment</h1>
            <a 
                href="{{ route('admin.apartments') }}"
                class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg transition"
            >
                ← Back to Apartments
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.apartments.update', $apartment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="col-span-1 md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700">Appartment Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $apartment->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Floor -->
                    <div>
                        <label for="floor" class="block text-sm font-medium text-gray-700">Floor</label>
                        <select name="floor" id="floor" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="ground" {{ old('floor', $apartment->floor) == 'ground' ? 'selected' : '' }}>Ground Floor</option>
                            <option value="upper" {{ old('floor', $apartment->floor) == 'upper' ? 'selected' : '' }}>Upper Floor</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="available" {{ old('status', $apartment->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="maintenance" {{ old('status', $apartment->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </div>

                    <!-- Price per Night -->
                    <div>
                        <label for="price_per_night" class="block text-sm font-medium text-gray-700">Price per Night ($)</label>
                        <input type="number" step="0.01" min="0" name="price_per_night" id="price_per_night" value="{{ old('price_per_night', $apartment->price_per_night) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Max Guests -->
                    <div>
                        <label for="max_guests" class="block text-sm font-medium text-gray-700">Max Guests</label>
                        <input type="number" min="1" name="max_guests" id="max_guests" value="{{ old('max_guests', $apartment->max_guests) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Bedrooms -->
                    <div>
                        <label for="bedrooms" class="block text-sm font-medium text-gray-700">Bedrooms</label>
                        <input type="number" min="0" name="bedrooms" id="bedrooms" value="{{ old('bedrooms', $apartment->bedrooms) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Bathrooms -->
                    <div>
                        <label for="bathrooms" class="block text-sm font-medium text-gray-700">Bathrooms</label>
                        <input type="number" min="0" name="bathrooms" id="bathrooms" value="{{ old('bathrooms', $apartment->bathrooms) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Description -->
                    <div class="col-span-1 md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $apartment->description) }}</textarea>
                    </div>

                    <!-- Upload New Images -->
                    <div class="col-span-1 md:col-span-2">
                        <label for="images" class="block text-sm font-medium text-gray-700">Upload Additional Images</label>
                        <p class="text-sm text-gray-500 mb-2">You can select multiple images to add.</p>
                        <input type="file" name="images[]" id="images" multiple accept="image/*" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-bold transition">
                        Save Changes
                    </button>
                </div>
            </form>
            
            <hr class="my-8">

            <h3 class="text-xl font-bold mb-4">Current Images</h3>
            @if($apartment->images->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($apartment->images as $image)
                        <div class="relative group">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Apartment Image" class="w-full h-32 object-cover rounded-lg show shadow">
                            <form action="{{ route('admin.apartments.images.destroy', $image->id) }}" method="POST" class="absolute top-2 right-2 hidden group-hover:block" onsubmit="return confirm('Delete this image?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-700 shadow-lg" title="Delete Image">
                                    ×
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No images uploaded for this apartment yet.</p>
            @endif

        </div>
    </div>
</div>
@endsection
