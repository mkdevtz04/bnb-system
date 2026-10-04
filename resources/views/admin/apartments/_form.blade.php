{{-- Shared by create and edit so the two forms cannot drift apart. --}}
@php
    $allAmenities = [
        'wifi' => 'Wi-Fi', 'kitchen' => 'Kitchen', 'air_conditioning' => 'Air conditioning',
        'parking' => 'Parking', 'workspace' => 'Workspace', 'washer' => 'Washer',
        'tv' => 'TV', 'balcony' => 'Balcony',
    ];
    $selected = old('amenities', $apartment->amenities ?? []);
@endphp

<div class="stack">
    <section class="card card-pad">
        <h2 style="font-size:18px; margin-bottom:16px;">Basics</h2>

        <div class="field">
            <label class="field-label" for="name">Property name</label>
            <input type="text" id="name" name="name" required maxlength="255"
                   class="input @error('name') input-error @enderror"
                   value="{{ old('name', $apartment->name) }}">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field mt-4">
            <label class="field-label" for="description">Description</label>
            <textarea id="description" name="description" rows="6" required
                      class="textarea @error('description') input-error @enderror">{{ old('description', $apartment->description) }}</textarea>
            @error('description') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-3 mt-4">
            <div class="field">
                <label class="field-label" for="address">Address</label>
                <input type="text" id="address" name="address" maxlength="255" class="input"
                       value="{{ old('address', $apartment->address) }}">
            </div>
            <div class="field">
                <label class="field-label" for="city">City</label>
                <input type="text" id="city" name="city" maxlength="120" class="input"
                       value="{{ old('city', $apartment->city) }}">
            </div>
            <div class="field">
                <label class="field-label" for="country">Country</label>
                <input type="text" id="country" name="country" maxlength="120" class="input"
                       value="{{ old('country', $apartment->country) }}">
            </div>
        </div>
    </section>

    <section class="card card-pad">
        <h2 style="font-size:18px; margin-bottom:16px;">Capacity &amp; layout</h2>
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="field">
                <label class="field-label" for="max_guests">Sleeps</label>
                <input type="number" id="max_guests" name="max_guests" min="1" max="30" required
                       class="input @error('max_guests') input-error @enderror"
                       value="{{ old('max_guests', $apartment->max_guests ?? 2) }}">
                @error('max_guests') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label class="field-label" for="bedrooms">Bedrooms</label>
                <input type="number" id="bedrooms" name="bedrooms" min="0" required class="input"
                       value="{{ old('bedrooms', $apartment->bedrooms ?? 1) }}">
            </div>
            <div class="field">
                <label class="field-label" for="bathrooms">Bathrooms</label>
                <input type="number" id="bathrooms" name="bathrooms" min="0" required class="input"
                       value="{{ old('bathrooms', $apartment->bathrooms ?? 1) }}">
            </div>
            <div class="field">
                <label class="field-label" for="floor">Floor</label>
                <select id="floor" name="floor" class="select" required>
                    <option value="ground" @selected(old('floor', $apartment->floor) === 'ground')>Ground</option>
                    <option value="upper" @selected(old('floor', $apartment->floor) === 'upper')>Upper</option>
                </select>
            </div>
        </div>

        <div class="field mt-4">
            <span class="field-label">Amenities</span>
            {{-- Stored per property. The guest-facing page used to hard-code the
                 same list onto every listing regardless of what was actually there. --}}
            <div class="grid gap-2 sm:grid-cols-3 mt-1">
                @foreach ($allAmenities as $key => $label)
                    <label class="flex items-center gap-2" style="font-size:14px;">
                        <input type="checkbox" name="amenities[]" value="{{ $key }}"
                               @checked(in_array($key, (array) $selected, true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
    </section>

    <section class="card card-pad">
        <h2 style="font-size:18px; margin-bottom:16px;">Pricing &amp; rules</h2>
        <div class="grid gap-4 sm:grid-cols-4">
            <div class="field">
                <label class="field-label" for="price_per_night">Nightly rate</label>
                <input type="number" id="price_per_night" name="price_per_night" min="0" step="0.01" required
                       class="input @error('price_per_night') input-error @enderror"
                       value="{{ old('price_per_night', $apartment->price_per_night) }}">
                @error('price_per_night') <p class="field-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label class="field-label" for="cleaning_fee">Cleaning fee</label>
                <input type="number" id="cleaning_fee" name="cleaning_fee" min="0" step="0.01" class="input"
                       value="{{ old('cleaning_fee', $apartment->cleaning_fee ?? 0) }}">
                <p class="field-hint">Added once per stay.</p>
            </div>
            <div class="field">
                <label class="field-label" for="min_nights">Minimum nights</label>
                <input type="number" id="min_nights" name="min_nights" min="1" max="30" class="input"
                       value="{{ old('min_nights', $apartment->min_nights ?? 1) }}">
            </div>
            <div class="field">
                <label class="field-label" for="status">Status</label>
                <select id="status" name="status" class="select" required>
                    <option value="available" @selected(old('status', $apartment->status) === 'available')>On sale</option>
                    <option value="maintenance" @selected(old('status', $apartment->status) === 'maintenance')>Maintenance</option>
                </select>
            </div>
        </div>

        <p class="field-hint mt-3">
            Service fee ({{ (int) (config('booking.service_fee_rate') * 100) }}%) and tax
            ({{ (int) (config('booking.tax_rate') * 100) }}%) are applied automatically at checkout.
        </p>
    </section>

    <section class="card card-pad">
        <h2 style="font-size:18px; margin-bottom:16px;">Photos</h2>

        @if (isset($apartment) && $apartment->exists && $apartment->images->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-4 mb-4">
                @foreach ($apartment->images as $image)
                    <div class="relative">
                        <img src="{{ Storage::url($image->image_path) }}" alt=""
                             class="w-full object-cover" style="aspect-ratio:1/1; border-radius:var(--r-md);">
                        <button type="button" class="btn btn-danger btn-sm"
                                style="position:absolute; top:6px; right:6px;"
                                onclick="if (confirm('Remove this photo?')) document.getElementById('del-img-{{ $image->id }}').submit()">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="field">
            <label class="field-label" for="images">Upload photos</label>
            <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                   class="input">
            <p class="field-hint">JPG, PNG or WebP, up to 5 MB each.</p>
            @error('images.*') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </section>
</div>
