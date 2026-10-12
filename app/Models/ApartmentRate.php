<?php

namespace App\Models;

use App\Casts\DateOnly;
use Illuminate\Database\Eloquent\Model;

/**
 * A per-night price override. A date with no row here simply costs the
 * apartment's base price_per_night — see PricingService::quote(), the only
 * place that reads this table to price a stay.
 */
class ApartmentRate extends Model
{
    protected $fillable = [
        'apartment_id',
        'date',
        'price_per_night',
    ];

    protected function casts(): array
    {
        return [
            'date' => DateOnly::class,
            'price_per_night' => 'decimal:2',
        ];
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
