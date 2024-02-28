<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'rol' => 'doctor',
            'name' => 'Docmario',
            'last_name' => 'Jimenez Rodriguez',
            'email' => 'docmario@example.com',
            'password' => bcrypt('1234567890'),
            'phone' => '1234567890',
            'sex' => 'male',
            'age' => 29,
            'date_of_birth' => '1990-01-01',
            'link_photo' => ''
        ]);

        $doctor = new Doctor([
            'user_id' => $user->id,
            'professional_license' => 'SITBVP9VMOAO',
            'education' => 'UNAM',
            'consultation_cost' => 500.00
        ]);
        $doctor->save();

        DB::table('doctor_establishment')->insert([
            'doctor_user_id' => 3,
            'establishment_id' => 1,
        ]);
    }
}
