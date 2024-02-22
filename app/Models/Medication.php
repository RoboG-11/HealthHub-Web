<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = ['medication_name', 'description'];

    // NOTE: Relación con la tabla patients - muchos a muchos
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class);
    }
}
