<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorSpecialty extends Model
{
    use HasFactory;

    protected $table = 'doctor_specialty';

    protected $fillable = [
        'doctor_user_id',
        'specialty_id'
    ];
}
