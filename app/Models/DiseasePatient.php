<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiseasePatient extends Model
{
    use HasFactory;

    protected $table = 'disease_patient';

    protected $fillable = [
        'patient_user_id',
        'disease_id'
    ];

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_user_id', 'user_id');
    }
}
