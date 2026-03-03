<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentImage extends Model
{
    use HasFactory;

    protected $table = 'apartment_images';

    protected $fillable = [
        'apartment_id',
        'image_path',
    ];

    /**
     * Get the apartment this image belongs to
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
