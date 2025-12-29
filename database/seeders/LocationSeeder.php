<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
 
class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Location::create([
            'name' => 'Store Anfa',
            'city' => 'Casablanca',
            'address' => 'Boulevard d\'Anfa, Résidence les Princes',
            'latitude' => 33.5951,
            'longitude' => -7.6322,
            'type' => 'branch'
        ]);
 
        \App\Models\Location::create([
            'name' => 'Store Maârif',
            'city' => 'Casablanca',
            'address' => 'Rue du Jura, Maârif',
            'latitude' => 33.5822,
            'longitude' => -7.6329,
            'type' => 'branch'
        ]);
 
        \App\Models\Location::create([
            'name' => 'Store Ain Diab',
            'city' => 'Casablanca',
            'address' => 'Corniche Ain Diab, Megarama',
            'latitude' => 33.5982,
            'longitude' => -7.6622,
            'type' => 'branch'
        ]);
 
        \App\Models\Location::create([
            'name' => 'Point Relais Rabat',
            'city' => 'Rabat',
            'address' => 'Avenue Mohammed V',
            'latitude' => 34.0209,
            'longitude' => -6.8416,
            'type' => 'relay'
        ]);
 
        \App\Models\Location::create([
            'name' => 'Point Relais Marrakech',
            'city' => 'Marrakech',
            'address' => 'Gueliz, Avenue Mohammed VI',
            'latitude' => 31.6295,
            'longitude' => -7.9811,
            'type' => 'relay'
        ]);

        \App\Models\Location::create([
            'name' => 'Point Relais Tanger',
            'city' => 'Tanger',
            'address' => 'Place des Nations',
            'latitude' => 35.7595,
            'longitude' => -5.8340,
            'type' => 'relay'
        ]);

        \App\Models\Location::create([
            'name' => 'Point Relais Agadir',
            'city' => 'Agadir',
            'address' => 'Avenue Mohammed V, Centre ville',
            'latitude' => 30.4278,
            'longitude' => -9.5981,
            'type' => 'relay'
        ]);
    }
}
