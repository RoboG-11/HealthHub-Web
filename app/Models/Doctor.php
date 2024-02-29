<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id', 'professional_license', 'education', 'consultation_cost'
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

    // NOTE: Relación con la tabla specialty - 1 a 1
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class);
    }

    // NOTE: Relación con la tabla establishment - 1 a 1
    public function establishments(): BelongsToMany
    {
        return $this->belongsToMany(Establishment::class);
    }

    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'doctor_id', 'user_id');
    }
}
