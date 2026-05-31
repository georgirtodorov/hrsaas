<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hrapp.app'],
            [
                'name' => 'Admin',
                'is_admin' => true,
                'password' => bcrypt('password'),
            ],
        );

        if ($admin->wasRecentlyCreated) {
            $team = Team::factory()->personal()->create([
                'name' => "{$admin->name}'s Team",
            ]);

            $team->members()->attach($admin, ['role' => TeamRole::Owner->value]);
            $admin->switchTeam($team);
        }

        $users = User::factory(10)->create();

        $allUsers = collect([$admin, ...$users]);
        $teams = Team::where('is_personal', false)->get();

        foreach ($allUsers as $user) {
            $assignedTeams = $teams->random(min(rand(0, 3), $teams->count()));

            foreach ($assignedTeams as $team) {
                $role = fake()->randomElement([TeamRole::Admin, TeamRole::Member]);

                $team->members()->attach($user, ['role' => $role->value]);
            }
        }
    }
}
