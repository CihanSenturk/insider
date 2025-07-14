<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\FootballMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TeamModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_team()
    {
        $team = Team::create(['name' => 'Liverpool']);
        
        $this->assertDatabaseHas('teams', ['name' => 'Liverpool']);
        $this->assertEquals('Liverpool', $team->name);
    }

    /** @test */
    public function it_has_home_matches_relationship()
    {
        $team1 = Team::create(['name' => 'Liverpool']);
        $team2 = Team::create(['name' => 'Arsenal']);
        
        FootballMatch::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'home_score' => 2,
            'away_score' => 1,
            'is_played' => true
        ]);
        
        $this->assertCount(1, $team1->homeMatches);
        $this->assertEquals($team2->id, $team1->homeMatches->first()->away_team_id);
    }

    /** @test */
    public function it_has_away_matches_relationship()
    {
        $team1 = Team::create(['name' => 'Liverpool']);
        $team2 = Team::create(['name' => 'Arsenal']);
        
        FootballMatch::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'home_score' => 2,
            'away_score' => 1,
            'is_played' => true
        ]);
        
        $this->assertCount(1, $team2->awayMatches);
        $this->assertEquals($team1->id, $team2->awayMatches->first()->home_team_id);
    }

    /** @test */
    public function it_validates_required_name()
    {
        $this->expectException(\Exception::class);
        Team::create([]);
    }
}
