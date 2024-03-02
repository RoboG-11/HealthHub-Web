<?php

namespace Database\Seeders;

use App\Models\Summary;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Summary::create([
            'appointment_id' => 1,
            'diagnosis' => 'Todo bien pa!',
            'medicine_id' => 2
        ]);
    }
}
