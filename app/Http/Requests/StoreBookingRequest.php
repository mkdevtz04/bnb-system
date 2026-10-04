<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Booking::class) ?? false;
    }

    public function rules(): array
    {
        $window = config('booking.booking_window_months', 12);

        return [
            'apartment_id' => ['required', 'integer', 'exists:apartments,id'],
            'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out' => [
                'required',
                'date_format:Y-m-d',
                'after:check_in',
                'before_or_equal:'.CarbonImmutable::today()->addMonths($window)->toDateString(),
            ],
            // The property page collected this and the server threw it away, so a
            // party of eight could book a studio that sleeps two.
            'guests' => ['required', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $nights = $this->checkIn()->diffInDays($this->checkOut());
            $maxNights = config('booking.max_nights', 30);

            if ($nights > $maxNights) {
                $validator->errors()->add('check_out', "Stays are limited to {$maxNights} nights.");
            }
        });
    }

    public function messages(): array
    {
        return [
            'check_in.after_or_equal' => 'Check-in cannot be in the past.',
            'check_out.after' => 'Check-out must be at least one night after check-in.',
            'check_out.before_or_equal' => 'That is further ahead than we currently take bookings.',
            'guests.required' => 'Tell us how many guests are staying.',
        ];
    }

    public function checkIn(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->validated('check_in'))->startOfDay();
    }

    public function checkOut(): CarbonImmutable
    {
        return CarbonImmutable::parse($this->validated('check_out'))->startOfDay();
    }

    public function guests(): int
    {
        return (int) $this->validated('guests');
    }
}
