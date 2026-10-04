@props([
    'checkIn' => null,
    'checkOut' => null,
    'guests' => null,
])

{{-- The one search control, used on the hero and pinned above results.
     .searchbar-wrap centres it, so no page has to do that itself. --}}
<div class="searchbar-wrap">
<form action="{{ route('apartments.search') }}" method="GET" class="searchbar" x-data>
    <label class="searchbar-field">
        <i class="fa-regular fa-calendar"></i>
        <span class="sr-only">Check-in</span>
        <input type="text" name="check_in" data-datepicker="start" autocomplete="off"
               placeholder="Check-in" value="{{ request('check_in', $checkIn?->toDateString()) }}">
    </label>

    <label class="searchbar-field">
        <i class="fa-regular fa-calendar-check"></i>
        <span class="sr-only">Check-out</span>
        <input type="text" name="check_out" data-datepicker="end" autocomplete="off"
               placeholder="Check-out" value="{{ request('check_out', $checkOut?->toDateString()) }}">
    </label>

    <label class="searchbar-field">
        <i class="fa-solid fa-user-group"></i>
        <span class="sr-only">Guests</span>
        <select name="guests">
            <option value="">Any guests</option>
            @for ($i = 1; $i <= 10; $i++)
                <option value="{{ $i }}" @selected((int) request('guests', $guests) === $i)>
                    {{ $i }} {{ Str::plural('guest', $i) }}
                </option>
            @endfor
        </select>
    </label>

    <button type="submit" class="btn btn-search btn-lg">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span>Search</span>
    </button>
</form>
</div>
