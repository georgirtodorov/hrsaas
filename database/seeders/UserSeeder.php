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

        $testUser = User::firstOrCreate(
            ['email' => 'test@hrapp.app'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
        );

        if ($testUser->wasRecentlyCreated) {
            $personalTeam = Team::factory()->personal()->create([
                'name' => "{$testUser->name}'s Team",
            ]);
            $personalTeam->members()->attach($testUser, ['role' => TeamRole::Owner->value]);
            $testUser->switchTeam($personalTeam);
        }

        $companies = Team::where('is_personal', false)->limit(3)->get();
        foreach ($companies as $team) {
            if (! $testUser->belongsToTeam($team)) {
                $team->members()->attach($testUser, ['role' => TeamRole::Admin->value]);
            }
        }

        $testUser->switchTeam($companies->first());

        $singleUser = User::firstOrCreate(
            ['email' => 'single@hrapp.app'],
            [
                'name' => 'Single Co User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
        );

        if ($singleUser->wasRecentlyCreated) {
            $personalTeam = Team::factory()->personal()->create([
                'name' => "{$singleUser->name}'s Team",
            ]);
            $personalTeam->members()->attach($singleUser, ['role' => TeamRole::Owner->value]);
            $singleUser->switchTeam($personalTeam);
        }

        $company = Team::where('is_personal', false)->first();
        if (! $singleUser->belongsToTeam($company)) {
            $company->members()->attach($singleUser, ['role' => TeamRole::Member->value]);
        }

        $singleUser->switchTeam($company);

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
