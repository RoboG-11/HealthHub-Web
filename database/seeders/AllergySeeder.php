<?php

namespace Database\Seeders;

use App\Models\Allergy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Schema::create('allergies', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('allergy_name');
        //     $table->text('description')->nullable();
        //     $table->timestamps();
        // });
        Allergy::create([
            'allergy_name' => 'Polen',
            'description' => 'Alergia a las partículas de polen.'
        ]);

        Allergy::create([
            'allergy_name' => 'Nueces',
            'description' => 'Alergia a las nueces.'
        ]);

        Allergy::create([
            'allergy_name' => 'Picaduras de insectos',
            'description' => 'Alergia a las picaduras de insectos.'
        ]);


        DB::table('allergy_patient')->insert([
            'patient_user_id' => 1,
            'allergy_id' => 1,
        ]);

        DB::table('allergy_patient')->insert([
            'patient_user_id' => 1,
            'allergy_id' => 2,
        ]);
    }
}
