<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Specialty::create([
            'specialty_name' => 'General',
            'description' => 'Es doctor general weee'
        ]);

        Specialty::create([
            'specialty_name' => 'Psicologo',
            'description' => 'Es psicologo we jsjsjs'
        ]);

        DB::table('doctor_specialty')->insert([
            'doctor_user_id' => 3,
            'specialty_id' => 1,
        ]);

        DB::table('doctor_specialty')->insert([
            'doctor_user_id' => 3,
            'specialty_id' => 2,
        ]);
    }
}
