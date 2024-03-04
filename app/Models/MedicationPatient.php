<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationPatient extends Model
{
    use HasFactory;

    protected $table = 'medication_patient';

    protected $fillable = [
        'patient_user_id',
        'medication_id'
    ];

    public function medication()
    {
        return $this->belongsTo(Medication::class, 'medication_id');
    }
}
