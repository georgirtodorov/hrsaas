<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $team = $user->teams()->first();

            if (! $team) {
                continue;
            }

            $department = Department::where('team_id', $team->id)->inRandomOrder()->first();

            Employee::factory()->create([
                'team_id' => $team->id,
                'user_id' => $user->id,
                'department_id' => $department?->id,
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'email' => $user->email,
            ]);
        }
    }
}
