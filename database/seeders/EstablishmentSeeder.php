<?php

namespace Database\Seeders;

use App\Models\Establishment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EstablishmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Establishment::create([
            'establishment_name' => 'Hospital ABC',
            'establishment_type' => 'Hospital',
            'website_url' => '',
            'address_id' => 1
        ]);

        Establishment::create([
            'establishment_name' => 'Hospital Angeles',
            'establishment_type' => 'Hospital',
            'website_url' => '',
            'address_id' => 2
        ]);
    }
}
