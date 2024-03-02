<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Medicine::create([
            'summary_id' => 1,
            'medicine_name' => 'Ibuprofen',
            'dosage' => '200mg',
            'frequency' => 'Twice daily',
            'duration' => '7 days',
            'notes' => 'Take with food',
        ]);

        Medicine::create([
            'summary_id' => 1,
            'medicine_name' => 'Paracetamol',
            'dosage' => '500mg',
            'frequency' => 'As needed',
            'duration' => '3 days',
            'notes' => 'Do not exceed recommended dose',
        ]);
    }
}
