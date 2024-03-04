<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorEstablishment extends Model
{
    use HasFactory;

    protected $table = 'doctor_establishment';

    protected $fillable = [
        'doctor_user_id',
        'establishment_id'
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }
}
