<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Team;
use App\Models\FootballMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FootballMatchModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_football_match()
    {
        $team1 = Team::create(['name' => 'Liverpool']);
        $team2 = Team::create(['name' => 'Arsenal']);
        
        $match = FootballMatch::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1,
            'home_score' => 2,
            'away_score' => 1,
            'is_played' => true
        ]);
        
        $this->assertDatabaseHas('football_matches', [
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1
        ]);
    }

    /** @test */
    public function it_has_home_team_relationship()
    {
        $team1 = Team::create(['name' => 'Liverpool']);
        $team2 = Team::create(['name' => 'Arsenal']);
        
        $match = FootballMatch::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1
        ]);
        
        $this->assertEquals('Liverpool', $match->homeTeam->name);
    }

    /** @test */
    public function it_has_away_team_relationship()
    {
        $team1 = Team::create(['name' => 'Liverpool']);
        $team2 = Team::create(['name' => 'Arsenal']);
        
        $match = FootballMatch::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1
        ]);
        
        $this->assertEquals('Arsenal', $match->awayTeam->name);
    }

    /** @test */
    public function it_defaults_is_played_to_false()
    {
        $team1 = Team::create(['name' => 'Liverpool']);
        $team2 = Team::create(['name' => 'Arsenal']);
        
        $match = FootballMatch::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1
        ]);
        
        $this->assertFalse($match->is_played);
    }

    /** @test */
    public function it_can_have_nullable_scores()
    {
        $team1 = Team::create(['name' => 'Liverpool']);
        $team2 = Team::create(['name' => 'Arsenal']);
        
        $match = FootballMatch::create([
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'week' => 1
        ]);
        
        $this->assertNull($match->home_score);
        $this->assertNull($match->away_score);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Exception::class);
        FootballMatch::create([]);
    }
}
