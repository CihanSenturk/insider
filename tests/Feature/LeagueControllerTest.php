<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Team;
use App\Models\FootballMatch;

class LeagueControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create teams manually for testing
        $teams = [
            ['name' => 'Manchester City', 'strength' => 92],
            ['name' => 'Arsenal', 'strength' => 87],
            ['name' => 'Liverpool', 'strength' => 85],
            ['name' => 'Tottenham', 'strength' => 78],
        ];
        
        foreach ($teams as $teamData) {
            Team::create($teamData);
        }
        
        // Disable CSRF protection for testing
        $this->withoutMiddleware();
    }

    /** @test */
    public function it_can_initialize_league()
    {
        $response = $this->post('/league/initialize');
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $this->assertEquals(4, Team::count());
        $this->assertEquals(12, FootballMatch::count());
    }

    /** @test */
    public function it_generates_correct_fixture_schedule()
    {
        $this->post('/league/initialize');
        
        // Check that we have exactly 12 matches across 6 weeks
        $this->assertEquals(12, FootballMatch::count());
        
        // Check each week has exactly 2 matches
        for ($week = 1; $week <= 6; $week++) {
            $weekMatches = FootballMatch::where('week', $week)->count();
            $this->assertEquals(2, $weekMatches, "Week $week should have exactly 2 matches");
        }
        
        // Check no team plays multiple matches in same week
        for ($week = 1; $week <= 6; $week++) {
            $weekMatches = FootballMatch::where('week', $week)->get();
            $teamsInWeek = [];
            
            foreach ($weekMatches as $match) {
                $teamsInWeek[] = $match->home_team_id;
                $teamsInWeek[] = $match->away_team_id;
            }
            
            // Should have 4 unique teams (no team plays twice in same week)
            $uniqueTeams = array_unique($teamsInWeek);
            $this->assertEquals(4, count($uniqueTeams), "Week $week should have all 4 teams playing exactly once");
            $this->assertEquals(count($teamsInWeek), count($uniqueTeams), "No team should play multiple matches in week $week");
        }
    }
}
