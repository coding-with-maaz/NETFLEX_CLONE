<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['code' => 'US', 'name' => 'United States', 'slug' => 'united-states'],
            ['code' => 'GB', 'name' => 'United Kingdom', 'slug' => 'united-kingdom'],
            ['code' => 'KR', 'name' => 'Korea', 'slug' => 'korea'],
            ['code' => 'JP', 'name' => 'Japan', 'slug' => 'japan'],
            ['code' => 'BD', 'name' => 'Bangladesh', 'slug' => 'bangladesh'],
            ['code' => 'CN', 'name' => 'China', 'slug' => 'china'],
            ['code' => 'EG', 'name' => 'Egypt', 'slug' => 'egypt'],
            ['code' => 'FR', 'name' => 'France', 'slug' => 'france'],
            ['code' => 'DE', 'name' => 'Germany', 'slug' => 'germany'],
            ['code' => 'IN', 'name' => 'India', 'slug' => 'india'],
            ['code' => 'ID', 'name' => 'Indonesia', 'slug' => 'indonesia'],
            ['code' => 'IQ', 'name' => 'Iraq', 'slug' => 'iraq'],
            ['code' => 'IT', 'name' => 'Italy', 'slug' => 'italy'],
            ['code' => 'CI', 'name' => 'Ivory Coast', 'slug' => 'ivory-coast'],
            ['code' => 'KE', 'name' => 'Kenya', 'slug' => 'kenya'],
            ['code' => 'LB', 'name' => 'Lebanon', 'slug' => 'lebanon'],
            ['code' => 'MX', 'name' => 'Mexico', 'slug' => 'mexico'],
            ['code' => 'MA', 'name' => 'Morocco', 'slug' => 'morocco'],
            ['code' => 'NG', 'name' => 'Nigeria', 'slug' => 'nigeria'],
            ['code' => 'PK', 'name' => 'Pakistan', 'slug' => 'pakistan'],
            ['code' => 'PH', 'name' => 'Philippines', 'slug' => 'philippines'],
            ['code' => 'RU', 'name' => 'Russia', 'slug' => 'russia'],
            ['code' => 'SA', 'name' => 'Saudi Arabia', 'slug' => 'saudi-arabia'],
            ['code' => 'ZA', 'name' => 'South Africa', 'slug' => 'south-africa'],
            ['code' => 'ES', 'name' => 'Spain', 'slug' => 'spain'],
            ['code' => 'SY', 'name' => 'Syria', 'slug' => 'syria'],
            ['code' => 'TH', 'name' => 'Thailand', 'slug' => 'thailand'],
            ['code' => 'TR', 'name' => 'Turkey', 'slug' => 'turkey'],
            ['code' => 'XX', 'name' => 'Other', 'slug' => 'other'],
        ];

        foreach ($countries as $country) {
            // Use code if available, otherwise use slug
            if ($country['code']) {
                Country::updateOrCreate(
                    ['code' => $country['code']],
                    $country
                );
            } else {
                Country::updateOrCreate(
                    ['slug' => $country['slug']],
                    $country
                );
            }
        }
    }
}

