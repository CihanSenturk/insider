<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\LeagueService;
use App\Models\Team;
use App\Models\FootballMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeagueServiceTest extends TestCase
{
    use RefreshDatabase;

    private $leagueService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leagueService = new LeagueService();
        
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
    }

    /** @test */
    public function it_can_initialize_league()
    {
        $this->leagueService->initializeLeague();
        
        $this->assertEquals(4, Team::count());
        
        $teams = Team::all();
        $expectedTeams = ['Liverpool', 'Manchester City', 'Arsenal', 'Tottenham'];
        
        foreach ($expectedTeams as $teamName) {
            $this->assertTrue($teams->pluck('name')->contains($teamName));
        }
        
        // Check if fixture is created
        $this->assertEquals(12, FootballMatch::count());
    }

    /** @test */
    public function it_creates_correct_fixture_for_4_teams()
    {
        $this->leagueService->initializeLeague();
        
        // initializeLeague already calls generateFixtures
        // 4 teams, each team plays others twice (home/away) = 6 weeks * 2 matches = 12 matches
        $this->assertEquals(12, FootballMatch::count());
        
        // Each week should have 2 matches (4 teams / 2 = 2 matches)
        for ($week = 1; $week <= 6; $week++) {
            $weekMatches = FootballMatch::where('week', $week)->count();
            $this->assertEquals(2, $weekMatches, "Week $week should have 2 matches");
        }
    }

    /** @test */
    public function it_ensures_each_team_plays_against_others_twice()
    {
        $this->leagueService->initializeLeague();
        
        $teams = Team::all();
        $allMatches = FootballMatch::all();
        
        // Each team should play other teams twice (1 home, 1 away)
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                $team1 = $teams[$i];
                $team2 = $teams[$j];
                
                // Find matches between these two teams
                $matchesBetween = $allMatches->filter(function($match) use ($team1, $team2) {
                    return ($match->home_team_id == $team1->id && $match->away_team_id == $team2->id) ||
                           ($match->home_team_id == $team2->id && $match->away_team_id == $team1->id);
                });
                
                // There should be exactly 2 matches between two teams
                $this->assertEquals(2, $matchesBetween->count(), 
                    "Teams {$team1->name} and {$team2->name} should play exactly 2 matches");
                
                // One should be home, one should be away
                $homeAwayCheck = $matchesBetween->some(function($match) use ($team1) {
                    return $match->home_team_id == $team1->id;
                }) && $matchesBetween->some(function($match) use ($team2) {
                    return $match->home_team_id == $team2->id;
                });
                
                $this->assertTrue($homeAwayCheck, 
                    "Teams {$team1->name} and {$team2->name} should play once home and once away");
            }
        }
    }

    /** @test */
    public function it_can_simulate_match()
    {
        $this->leagueService->initializeLeague();
        
        $match = FootballMatch::first();
        $originalStatus = $match->is_played;
        
        $this->leagueService->simulateMatch($match);
        
        $match->refresh();
        
        $this->assertTrue($match->is_played);
        $this->assertNotNull($match->home_score);
        $this->assertNotNull($match->away_score);
        $this->assertGreaterThanOrEqual(0, $match->home_score);
        $this->assertGreaterThanOrEqual(0, $match->away_score);
        $this->assertLessThanOrEqual(6, $match->home_score);
        $this->assertLessThanOrEqual(6, $match->away_score);
    }

    /** @test */
    public function it_generates_prediction_only_after_week_4()
    {
        $this->leagueService->initializeLeague();
        
        // Play matches up to week 3
        $matches = FootballMatch::where('week', '<=', 3)->get();
        foreach ($matches as $match) {
            $match->update([
                'home_score' => rand(0, 3),
                'away_score' => rand(0, 3),
                'is_played' => true
            ]);
        }
        
        $prediction = $this->leagueService->getPrediction();
        $this->assertNull($prediction);
        
        // Play week 4 match
        $week4Match = FootballMatch::where('week', 4)->first();
        $week4Match->update([
            'home_score' => 2,
            'away_score' => 0,
            'is_played' => true
        ]);
        
        $prediction = $this->leagueService->getPrediction();
        $this->assertNotNull($prediction);
        $this->assertArrayHasKey('message', $prediction);
        $this->assertArrayHasKey('leader', $prediction);
    }

    /** @test */
    public function it_ensures_no_team_plays_multiple_matches_per_week()
    {
        $this->leagueService->initializeLeague();
        
        // Check team matchups for each week
        for ($week = 1; $week <= 6; $week++) {
            $weekMatches = FootballMatch::where('week', $week)->get();
            
            // Each week should have exactly 2 matches
            $this->assertEquals(2, $weekMatches->count(), "Week $week should have exactly 2 matches");
            
            $teamsInWeek = [];
            foreach ($weekMatches as $match) {
                // Team cannot play against itself
                $this->assertNotEquals($match->home_team_id, $match->away_team_id, 
                    "Team cannot play against itself in week $week");
                
                // Which teams are playing this week
                $teamsInWeek[] = $match->home_team_id;
                $teamsInWeek[] = $match->away_team_id;
            }
            
            // Each team should play only once per week (4 unique team IDs)
            $uniqueTeams = array_unique($teamsInWeek);
            $this->assertEquals(4, count($uniqueTeams), 
                "All 4 teams should play exactly once in week $week");
            
            // No team should play multiple matches in the same week
            $this->assertEquals(count($teamsInWeek), count($uniqueTeams), 
                "No team should play multiple matches in week $week");
        }
    }

    /** @test */
    public function it_ensures_proper_home_away_distribution()
    {
        $this->leagueService->initializeLeague();
        
        $teams = Team::all();
        
        foreach ($teams as $team) {
            $homeMatches = FootballMatch::where('home_team_id', $team->id)->count();
            $awayMatches = FootballMatch::where('away_team_id', $team->id)->count();
            
            // Each team should play 3 home and 3 away matches
            $this->assertEquals(3, $homeMatches, 
                "Team {$team->name} should play exactly 3 home matches");
            $this->assertEquals(3, $awayMatches, 
                "Team {$team->name} should play exactly 3 away matches");
        }
    }
}
