<?php

namespace Database\Seeders;

use App\Models\Medication;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Medication::create([
            'medication_name' => 'chocho',
            'description' => 'Pa ponerse mamado',
        ]);

        DB::table('medication_patient')->insert([
            'patient_user_id' => 1,
            'medication_id' => 1,
        ]);
    }
}
