<?php

namespace Database\Seeders;

use App\Models\Disease;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiseaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Disease::create([
            'disease_name' => 'miopia',
            'description' => 'No veo weeeee'
        ]);

        DB::table('disease_patient')->insert([
            'patient_user_id' => 1,
            'disease_id' => 1,
        ]);
    }
}
