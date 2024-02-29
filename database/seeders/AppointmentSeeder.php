<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Appointment::create([
            'doctor_id' => 3,
            'patient_id' => 1,
            'appointment_datetime' => '2024-02-28',
            'link' => null,
            'status' => 'scheduled',
            'Reason' => 'consulta general',
            'consultation_cost' => 500.00
        ]);
    }
}
