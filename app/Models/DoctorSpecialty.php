<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSpecialty extends Model
{
    use HasFactory;

    protected $table = 'doctor_specialty';

    protected $fillable = [
        'doctor_user_id',
        'specialty_id'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_user_id', 'user_id');
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }
}
