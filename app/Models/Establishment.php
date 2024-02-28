<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Establishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'establishment_name', 'establishment_type', 'website_url', 'address_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // NOTE: Relación con la tabla addresses - uno a uno
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    // NOTE: Relación con la tabla doctor - muchos a muchos
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class);
    }
}
