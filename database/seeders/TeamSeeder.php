<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['name' => 'Acme Corp', 'slug' => 'acme-corp'],
            ['name' => 'Globex Inc', 'slug' => 'globex-inc'],
            ['name' => 'Initech', 'slug' => 'initech'],
            ['name' => 'Umbrella Co', 'slug' => 'umbrella-co'],
            ['name' => 'Stark Industries', 'slug' => 'stark-industries'],
        ];

        foreach ($teams as $team) {
            Team::create([
                'name' => $team['name'],
                'slug' => $team['slug'],
                'is_personal' => false,
                'is_active' => true,
            ]);
        }
    }
}
