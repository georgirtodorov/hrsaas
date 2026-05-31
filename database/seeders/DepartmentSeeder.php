<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Team;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departmentsByTeam = [
            'Acme Corp' => ['Engineering', 'Marketing', 'Sales', 'Human Resources'],
            'Globex Inc' => ['Engineering', 'Design', 'Finance'],
            'Initech' => ['Engineering', 'Sales', 'Operations'],
            'Umbrella Co' => ['Research & Development', 'Security', 'Medical'],
            'Stark Industries' => ['Engineering', 'R&D', 'Legal', 'Public Relations'],
        ];

        foreach ($departmentsByTeam as $teamName => $departments) {
            $team = Team::where('name', $teamName)->first();

            if (! $team) {
                continue;
            }

            foreach ($departments as $name) {
                Department::create([
                    'team_id' => $team->id,
                    'name' => $name,
                ]);
            }
        }
    }
}
