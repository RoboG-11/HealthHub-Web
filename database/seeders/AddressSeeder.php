<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Address::create([
            'street' => 'Calle Central del Sol',
            'interior_number' => 20,
            'exterior_number' => 200,
            'neighborhood' => 'Cuajimalpa',
            'zip_code' => 05300,
            'city' => 'CDMX',
            'country' => 'México'
        ]);

        Address::create([
            'street' => 'Lomas de Cocoyoc',
            'interior_number' => 25,
            'exterior_number' => 500,
            'neighborhood' => 'Cocoyoc',
            'zip_code' => 9043,
            'city' => 'Morelos',
            'country' => 'México'
        ]);
    }
}
