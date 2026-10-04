<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Search is a public, link-shareable surface: every filter lives in the query
 * string. Bad input is normalised away rather than shown as an error page.
 */
class SearchApartmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in' => ['nullable', 'date_format:Y-m-d'],
            'check_out' => ['nullable', 'date_format:Y-m-d', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:30'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_score' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'floor' => ['nullable', Rule::in(['ground', 'upper'])],
            'sort' => ['nullable', Rule::in(['recommended', 'price_asc', 'price_desc', 'score'])],
        ];
    }

    /**
     * Drop a past or incomplete date pair instead of rejecting the request.
     */
    protected function prepareForValidation(): void
    {
        $checkIn = $this->query('check_in');
        $checkOut = $this->query('check_out');

        if ($checkIn && $checkOut && $checkIn >= $checkOut) {
            $this->merge(['check_in' => null, 'check_out' => null]);
        }
    }

    public function checkIn(): ?CarbonImmutable
    {
        $value = $this->validated('check_in');

        if (! $value) {
            return null;
        }

        // Someone bookmarking a search from last month should see this month.
        return CarbonImmutable::parse($value)->startOfDay()->max(CarbonImmutable::today());
    }

    public function checkOut(): ?CarbonImmutable
    {
        $value = $this->validated('check_out');

        if (! $value || ! $this->validated('check_in')) {
            return null;
        }

        $checkOut = CarbonImmutable::parse($value)->startOfDay();

        return $checkOut > $this->checkIn() ? $checkOut : null;
    }

    public function guests(): ?int
    {
        return $this->validated('guests') ? (int) $this->validated('guests') : null;
    }

    public function bedrooms(): ?int
    {
        return $this->validated('bedrooms') ? (int) $this->validated('bedrooms') : null;
    }

    public function minPrice(): ?float
    {
        return $this->validated('min_price') !== null ? (float) $this->validated('min_price') : null;
    }

    public function maxPrice(): ?float
    {
        return $this->validated('max_price') !== null ? (float) $this->validated('max_price') : null;
    }

    public function minScore(): ?float
    {
        return $this->validated('min_score') !== null ? (float) $this->validated('min_score') : null;
    }

    public function floor(): ?string
    {
        return $this->validated('floor');
    }

    public function sort(): string
    {
        return $this->validated('sort') ?: 'recommended';
    }
}
