<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Patient extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id', 'weight', 'height', 'nss', 'occupation', 'blood_type', 'emergency_contact_phone'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    // NOTE: Relación con la tabla users - 1 a 1
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // NOTE: Relación con la tabla allergy - muchos a muchos
    public function allergies(): BelongsToMany
    {
        return $this->belongsToMany(Allergy::class);
    }

    // NOTE: Relación con la tabla diseases - muchos a muchos
    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(Disease::class);
    }

    // NOTE: Relación con la tabla medications - muchos a muchos
    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class);
    }
}
