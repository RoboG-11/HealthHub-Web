<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'rol' => 'patient',
            'name' => 'Brian',
            'last_name' => 'Rivera Martinez',
            'email' => 'brian@example.com',
            'password' => bcrypt('1234567890'),
            'phone' => '1234567890',
            'sex' => 'male',
            'age' => 23,
            'date_of_birth' => '1990-01-01',
            'link_photo' => ''
        ]);

        $patient = new Patient([
            'user_id' => $user->id,
            'weight' => 60.5,
            'height' => 170.5,
            'nss' => 'BRIMA1085NFO048',
            'occupation' => 'estudihambre',
            'blood_type' => 'O+',
            'emergency_contact_phone' => '1234567890'
        ]);
        $patient->save();

        $user = User::create([
            'rol' => 'patient',
            'name' => 'Juan',
            'last_name' => 'Escutia Jimenez',
            'email' => 'juan@example.com',
            'password' => bcrypt('1234567890'),
            'phone' => '1234567890',
            'sex' => 'male',
            'age' => 23,
            'date_of_birth' => '1990-01-01',
            'link_photo' => ''
        ]);

        $patient = new Patient([
            'user_id' => $user->id,
            'weight' => 60.5,
            'height' => 170.5,
            'nss' => 'JES00185NGO0',
            'occupation' => 'estudihambre',
            'blood_type' => 'O+',
            'emergency_contact_phone' => '1234567890'
        ]);
        $patient->save();
    }
}
