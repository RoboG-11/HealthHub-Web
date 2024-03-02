<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'patient_id', 'appointment_datetime', 'link', 'status', 'reason', 'consultation_cost'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function summary(): HasOne
    {
        return $this->hasOne(Summary::class, 'appointment_id');
    }
}
