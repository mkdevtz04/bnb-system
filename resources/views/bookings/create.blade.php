@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Booking Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8">Complete Your Booking</h1>

                    <form action="{{ route('bookings.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <input type="hidden" name="apartment_id" value="{{ $apartment->id }}">

                        <!-- Check-in Date -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Check-in Date</label>
                            <input 
                                type="text" 
                                id="check_in" 
                                name="check_in" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Select check-in date"
                            >
                            @error('check_in')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Check-out Date -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Check-out Date</label>
                            <input 
                                type="text" 
                                id="check_out" 
                                name="check_out" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Select check-out date"
                            >
                            @error('check_out')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Guest Info -->
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <h3 class="font-bold text-gray-900 mb-4">Guest Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input 
                                        type="text" 
                                        value="{{ auth()->user()->name }}"
                                        disabled
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input 
                                        type="email" 
                                        value="{{ auth()->user()->email }}"
                                        disabled
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input 
                                        type="tel" 
                                        value="{{ auth()->user()->phone ?? 'Not provided' }}"
                                        disabled
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="flex items-start gap-3">
                            <input type="checkbox" id="terms" name="terms" required class="mt-1">
                            <label for="terms" class="text-gray-700 text-sm">
                                I agree to the <span class="font-bold">booking terms and conditions</span>. Your booking is pending admin confirmation.
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition"
                        >
                            Confirm Booking
                        </button>
                    </form>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Booking Summary</h2>

                    <!-- Apartment Info -->
                    <div class="mb-6 pb-6 border-b">
                        <h3 class="font-bold text-gray-900 mb-2">{{ $apartment->name }}</h3>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>🛏️ {{ $apartment->bedrooms }} Bedrooms</p>
                            <p>🚿 {{ $apartment->bathrooms }} Bathrooms</p>
                            <p>👥 {{ $apartment->max_guests }} Guests</p>
                            <p>📍 Floor: <span class="capitalize">{{ $apartment->floor }}</span></p>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="space-y-3 mb-6 pb-6 border-b">
                        <div class="flex justify-between text-gray-700">
                            <span>Price per night</span>
                            <span>${{ number_format($apartment->price_per_night) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span id="nights-label">Nights (0)</span>
                            <span id="nights-price">$0</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Service Fee</span>
                            <span id="service-fee">$0</span>
                        </div>
                        <div class="flex justify-between text-gray-700">
                            <span>Tax (10%)</span>
                            <span id="tax">$0</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="text-center mb-6">
                        <p class="text-gray-600 text-sm mb-1">Total Price</p>
                        <p class="text-4xl font-bold text-gray-900" id="total-price">$0</p>
                    </div>

                    <!-- Status -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                        ⏳ Your booking will be <span class="font-bold">pending admin confirmation</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flatpickr Integration -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    const pricePerNight = {{ $apartment->price_per_night }};

    const checkInPicker = flatpickr("#check_in", {
        minDate: "today",
        dateFormat: "Y-m-d",
        disableMobile: true,
        onChange: updatePrice
    });

    const checkOutPicker = flatpickr("#check_out", {
        minDate: "today",
        dateFormat: "Y-m-d",
        disableMobile: true,
        onChange: updatePrice
    });

    function updatePrice() {
        const checkIn = checkInPicker.selectedDates[0];
        const checkOut = checkOutPicker.selectedDates[0];

        if (!checkIn || !checkOut) {
            document.getElementById('nights-label').innerText = 'Nights (0)';
            document.getElementById('nights-price').innerText = '$0';
            document.getElementById('service-fee').innerText = '$0';
            document.getElementById('tax').innerText = '$0';
            document.getElementById('total-price').innerText = '$0';
            return;
        }

        const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
        const subtotal = nights * pricePerNight;
        const serviceFee = Math.round(subtotal * 0.05);
        const tax = Math.round((subtotal + serviceFee) * 0.10);
        const total = subtotal + serviceFee + tax;

        document.getElementById('nights-label').innerText = `Nights (${nights})`;
        document.getElementById('nights-price').innerText = `$${subtotal.toLocaleString()}`;
        document.getElementById('service-fee').innerText = `$${serviceFee.toLocaleString()}`;
        document.getElementById('tax').innerText = `$${tax.toLocaleString()}`;
        document.getElementById('total-price').innerText = `$${total.toLocaleString()}`;
    }
</script>
@endsection
