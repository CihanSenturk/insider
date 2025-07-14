<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * TeamSeeder
 * 
 * This seeder adds real Premier League teams and their strength levels
 * to the database. Team strengths are based on real performances.
 */
class TeamSeeder extends Seeder
{
    /**
     * Seeds Premier League teams to the database
     */
    public function run(): void
    {
        // Top 4 strongest Premier League teams
        $premierLeagueTeams = [
            [
                'name' => 'Manchester City',
                'strength' => 92,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Arsenal',
                'strength' => 87,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Liverpool',
                'strength' => 85,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tottenham',
                'strength' => 78,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        // Bulk insert teams to database
        Team::insert($premierLeagueTeams);

        $this->command->info('Premier League teams successfully added!');
        $this->command->info('Added teams:');
        
        foreach ($premierLeagueTeams as $team) {
            $this->command->info("{$team['name']} (Strength: {$team['strength']})");
        }
    }
}
