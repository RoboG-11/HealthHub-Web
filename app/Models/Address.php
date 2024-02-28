<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'street', 'interior_number', 'exterior_number', 'neighborhood', 'zip_code', 'city', 'country'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // NOTE: Relación con la tabla establishments - uno a uno
    public function establishment(): HasOne
    {
        return $this->hasOne(Establishment::class);
    }
}
