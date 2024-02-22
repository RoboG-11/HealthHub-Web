<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergyPatient extends Model
{
    use HasFactory;

    protected $table = 'allergy_patient';

    protected $fillable = [
        'patient_user_id',
        'allergy_id'
    ];
}
