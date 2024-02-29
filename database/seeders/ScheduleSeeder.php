<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schedule::create([
            'doctor_id' => 3,
            'start_time' => null,
            'end_time' => null,
            'day_of_week' => 'Lunes',
        ]);

        Schedule::create([
            'doctor_id' => 3,
            'start_time' => null,
            'end_time' => null,
            'day_of_week' => 'Martes',
        ]);

        Schedule::create([
            'doctor_id' => 3,
            'start_time' => null,
            'end_time' => null,
            'day_of_week' => 'Miercoles',
        ]);

        Schedule::create([
            'doctor_id' => 3,
            'start_time' => null,
            'end_time' => null,
            'day_of_week' => 'Jueves',
        ]);

        Schedule::create([
            'doctor_id' => 3,
            'start_time' => null,
            'end_time' => null,
            'day_of_week' => 'Viernes',
        ]);

        Schedule::create([
            'doctor_id' => 3,
            'start_time' => null,
            'end_time' => null,
            'day_of_week' => 'Sabado',
        ]);

        Schedule::create([
            'doctor_id' => 3,
            'start_time' => null,
            'end_time' => null,
            'day_of_week' => 'Domingo',
        ]);
    }
}
