<?php

use App\Entities\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countryIds = collect($this->getCountries())
            ->map(function(array $row) {
                $country = Country::firstOrNew(['name' => $row[0]]);
                $country->short_name = $row[1] ?? null;
                $country->dial_code = $row[2] ?? null;

                $country->save();

                return $country->id;
            });

        Country::whereNotIn('id', $countryIds->all())->delete();
    }

    private function getCountries(): array
    {
        return [
            ['Ghana', 'GH', '233'],
            ['Burkina Faso', 'BF', '226'],
            ['Nigeria', 'NG', '234'],
        ];
    }
}
