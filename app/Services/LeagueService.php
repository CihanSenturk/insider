<?php

namespace App\Services;

use App\Jobs\Matches\CreateMatch;
use App\Jobs\Matches\UpdateMatch;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Jobs\Teams\UpdateTeam;
use App\Traits\Jobs;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * This service class manages all business logic for Premier League simulation.
 * League initialization, fixture generation, match simulation, statistics
 * calculation and prediction algorithms are contained here.
 */
class LeagueService
{
    use Jobs;

    /**
     * Calculates goals based on team strengths
     * 
     * @param int $team_strength Team strength points
     * @param int $opponent_strength Opponent team strength points
     * @param bool $is_home Is it home team?
     * @return int Calculated number of goals
     */
    private function calculateGoals(int $team_strength, int $opponent_strength, bool $is_home): int
    {
        // Base probability based on strength difference
        $strength_diff = $team_strength - $opponent_strength;

        // Home advantage
        if ($is_home) {
            $strength_diff += 5;
        }

        // Convert strength to goal probability
        $base_goals = max(0, min(5, 2 + ($strength_diff / 20)));

        // Add randomness using Poisson distribution simulation
        $goals = 0;
        $random = mt_rand(1, 100);
        
        if ($random <= 15) {
            $goals = 0;
        } elseif ($random <= 40) {
            $goals = 1;
        } elseif ($random <= 70) {
            $goals = 2;
        } elseif ($random <= 90) {
            $goals = 3;
        } elseif ($random <= 97) {
            $goals = 4;
        } else {
            $goals = 5;
        }

        // Adjust based on team strength
        if ($base_goals > 2.5 && $goals > 0) {
            $goals = min(5, $goals + 1);
        } elseif ($base_goals < 1.5 && $goals > 1) {
            $goals = max(0, $goals - 1);
        }

        return $goals;
    }

    /**
     * Calculates head-to-head result between two teams
     * 
     * @param int $team_a_Id First team ID
     * @param int $team_b_Id Second team ID
     * @param int $up_to_week Up to which week to calculate
     * @return int Positive: A team ahead, Negative: B team ahead, 0: Draw
     */
    private function calculateHeadToHead(int $team_a_Id, int $team_b_Id, int $up_to_week): int
    {
        $matches = FootballMatch::where('week', '<=', $up_to_week)
            ->where('is_played', true)
            ->where(function ($query) use ($team_a_Id, $team_b_Id) {
                $query->where(function ($q) use ($team_a_Id, $team_b_Id) {
                    $q->where('home_team_id', $team_a_Id)
                      ->where('away_team_id', $team_b_Id);
                })->orWhere(function ($q) use ($team_a_Id, $team_b_Id) {
                    $q->where('home_team_id', $team_b_Id)
                      ->where('away_team_id', $team_a_Id);
                });
            })
            ->get();
         
        if ($matches->isEmpty()) {
            return 0;
        }

        $team_a_Points = 0;
        $team_b_Points = 0;
        $team_a_Goals = 0;
        $team_b_Goals = 0;

        foreach ($matches as $match) {
            if ($match->home_team_id === $team_a_Id) {
                $team_a_Goals += $match->home_score;
                $team_b_Goals += $match->away_score;
             
                if ($match->home_score > $match->away_score) {
                    $team_a_Points += 3;
                } elseif ($match->home_score < $match->away_score) {
                    $team_b_Points += 3;
                } else {
                    $team_a_Points += 1;
                    $team_b_Points += 1;
                }
            } else {
                $team_b_Goals += $match->home_score;
                $team_a_Goals += $match->away_score;

                if ($match->home_score > $match->away_score) {
                    $team_b_Points += 3;
                } elseif ($match->home_score < $match->away_score) {
                    $team_a_Points += 3;
                } else {
                    $team_a_Points += 1;
                    $team_b_Points += 1;
                }
            }
        }
     
        if ($team_a_Points !== $team_b_Points) {
            return $team_b_Points <=> $team_a_Points;
        }
     
        $team_a_Diff = $team_a_Goals - $team_b_Goals;
        $team_b_Diff = $team_b_Goals - $team_a_Goals;

        if ($team_a_Diff !== $team_b_Diff) {
            return $team_b_Diff <=> $team_a_Diff;
        }
     
        if ($team_a_Goals !== $team_b_Goals) {
            return $team_b_Goals <=> $team_a_Goals;
        }
     
        return 0;
    }

    /**
     * Creates professional fixtures
     * 
     * 
     * This method creates balanced fixtures like real football leagues.
     * 1 match per week. Perfect 6-week fixture for 4 teams.
     */
    private function generateFixtures(): void
    {
        $teams = Team::all();

        // Randomize teams but preserve leg structure
        $team_list = $teams->shuffle()->values()->toArray();
        
        $fixtures = [];
        
        $first_leg_template = [
            1 => [
                ['home' => 0, 'away' => 1],
                ['home' => 2, 'away' => 3]
            ],
            2 => [
                ['home' => 0, 'away' => 2],
                ['home' => 1, 'away' => 3]
            ],
            3 => [
                ['home' => 0, 'away' => 3],
                ['home' => 1, 'away' => 2] 
            ]
        ];
        
        // Create first leg matches
        foreach ($first_leg_template as $week => $matches) {
            $fixtures[$week] = [];
            foreach ($matches as $match) {
                $fixtures[$week][] = [
                    'home' => $team_list[$match['home']]['id'],
                    'away' => $team_list[$match['away']]['id']
                ];
            }
        }

        $return_matches = [];

        // Create return matches from first leg
        foreach ($first_leg_template as $week => $matches) {
            foreach ($matches as $match) {
                $return_matches[] = [
                    'home' => $team_list[$match['away']]['id'],
                    'away' => $team_list[$match['home']]['id'],
                    'original_week' => $week
                ];
            }
        }
        
        // Shuffle return matches
        shuffle($return_matches);

        // Initialize second leg weeks (4, 5, 6)
        $fixtures[4] = [];
        $fixtures[5] = [];
        $fixtures[6] = [];
        
        // Track which teams play each week
        $weekly_teams = [
            4 => [],
            5 => [],
            6 => []
        ];
        
        // Intelligently distribute return matches
        foreach ($return_matches as $match) {
            $home_team_id = $match['home'];
            $away_team_id = $match['away'];
            $placed = false;
            
            // Try weeks 4, 5, 6 in order
            for ($week = 4; $week <= 6; $week++) {
                if (count($fixtures[$week]) < 2 && 
                    !in_array($home_team_id, $weekly_teams[$week]) && 
                    !in_array($away_team_id, $weekly_teams[$week])) {
                    
                    $fixtures[$week][] = [
                        'home' => $home_team_id,
                        'away' => $away_team_id
                    ];

                    $weekly_teams[$week][] = $home_team_id;
                    $weekly_teams[$week][] = $away_team_id;
                    $placed = true;
                    break;
                }
            }
            
            // If above algorithm fails, force placement
            if (!$placed) {
                for ($week = 4; $week <= 6; $week++) {
                    if (count($fixtures[$week]) < 2) {
                        $fixtures[$week][] = [
                            'home' => $home_team_id,
                            'away' => $away_team_id
                        ];
                        break;
                    }
                }
            }
        }

        // Save fixtures to database professionally
        foreach ($fixtures as $week_number => $week_matches) {
            foreach ($week_matches as $match) {
                $this->ajaxDispatch(new CreateMatch([
                    'home_team_id' => $match['home'],
                    'away_team_id' => $match['away'],
                    'week' => $week_number,
                    'is_played' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Returns current week
     * 
     * @return int Current week (1-6) or 6 when league is finished
     */
    public function getCurrentWeek(): int
    {
        $total_matches = FootballMatch::count();
        $played_matches = FootballMatch::where('is_played', true)->count();

        if ($total_matches > 0 && $played_matches >= $total_matches) {
            return 6;
        }

        $last_played_week = FootballMatch::where('is_played', true)
            ->max('week');

        return $last_played_week ? $last_played_week + 1 : 1;
    }

    /**
     * Returns league table sorted by current standings
     * 
     * @return Collection Sorted teams
     */
    public function getLeagueTable(): Collection
    {
        $current_week = $this->getCurrentWeek();
        $max_week = min(6, $current_week);

        return $this->getLeagueTableUpToWeek($max_week);
    }

    /**
     * Calculates league table up to a specific week
     * 
     * @param int $up_to_week Up to which week to calculate
     * @return Collection Week-based league table
     */
    public function getLeagueTableUpToWeek(int $up_to_week): Collection
    {
        $teams = Team::all();
        
        if ($teams->isEmpty()) {
            return collect();
        }

        $team_stats = [];
        foreach ($teams as $team) {
            $team_stats[$team->id] = [
                'id' => $team->id,
                'name' => $team->name,
                'played' => 0,
                'won' => 0,
                'drawn' => 0,
                'lost' => 0,
                'goals_for' => 0,
                'goals_against' => 0,
                'goal_difference' => 0,
                'points' => 0,
            ];
        }

        $matches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('week', '<=', $up_to_week)
            ->where('is_played', true)
            ->get();

        foreach ($matches as $match) {
            $home_id = $match->home_team_id;
            $away_id = $match->away_team_id;
            $home_score = $match->home_score;
            $away_score = $match->away_score;

            $team_stats[$home_id]['played']++;
            $team_stats[$away_id]['played']++;

            $team_stats[$home_id]['goals_for'] += $home_score;
            $team_stats[$home_id]['goals_against'] += $away_score;
            $team_stats[$away_id]['goals_for'] += $away_score;
            $team_stats[$away_id]['goals_against'] += $home_score;

            if ($home_score > $away_score) {
                $team_stats[$home_id]['won']++;
                $team_stats[$home_id]['points'] += 3;
                $team_stats[$away_id]['lost']++;
            } elseif ($home_score < $away_score) {
                $team_stats[$away_id]['won']++;
                $team_stats[$away_id]['points'] += 3;
                $team_stats[$home_id]['lost']++;
            } else {
                $team_stats[$home_id]['drawn']++;
                $team_stats[$home_id]['points'] += 1;
                $team_stats[$away_id]['drawn']++;
                $team_stats[$away_id]['points'] += 1;
            }

            $team_stats[$home_id]['goal_difference'] = 
                $team_stats[$home_id]['goals_for'] - $team_stats[$home_id]['goals_against'];
            $team_stats[$away_id]['goal_difference'] = 
                $team_stats[$away_id]['goals_for'] - $team_stats[$away_id]['goals_against'];
        }

        $teams = collect($team_stats)
            ->map(fn($stats) => (object) $stats)
            ->values();

        return $this->sortByPremierLeagueRules($teams, $up_to_week);
    }

    /**
     * Get matches by week
     * 
     * @param int $week Week number
     * @return Collection Matches for the week
     */
    public function getMatchesByWeek(int $week): Collection
    {
        return FootballMatch::where('week', $week)
            ->with(['homeTeam', 'awayTeam'])
            ->get();
    }

    /**
     * Get prediction information
     * 
     * @return array|null Prediction information
     */
    public function getPrediction(): ?array
    {
        $current_week = $this->getCurrentWeek();

        if ($current_week <= 4) {
            return null;
        }
     
        return $this->predictLeagueWinner();
    }

    /**
     * Returns prediction information for a specific week
     * 
     * @param int $week Week number
     * @return array|null Prediction information
     */
    public function getPredictionForWeek(int $week): ?array
    {
        if ($week < 4) {
            return null;
        }

        $table = $this->getLeagueTableUpToWeek($week);
        
        if ($table->isEmpty()) {
            return null;
        }

        $leader = $table->first();
        $total_points = $table->sum('points');

        if ($total_points === 0) {
            return null;
        }

        $leader_percentage = round(($leader->points / $total_points) * 100);
        
        $weekly_messages = [
            4 => trans('league.prediction_list.week_4_early'),
            5 => trans('league.prediction_list.week_5_updated'), 
            6 => trans('league.prediction_list.final_standings')
        ];
        
        return [
            'show_prediction' => true,
            'message' => $weekly_messages[$week] ?? trans('league.prediction_list.week_prediction', ['week' => $week]),
            'winner' => $leader,
            'probability' => $leader_percentage,
            'week' => $week,
            'leader' => $leader->name,
            'points' => $leader->points
        ];
    }

    /**
     * Get week-specific data
     * 
     * @param int $week Week number
     * @return array Week data
     */
    public function getWeekData(int $week): array
    {
        $all_matches = $this->getMatchesByWeek($week);
        $selected_week_matches = $all_matches[$week] ?? collect();

        $table = $this->getLeagueTable();
        $prediction = $this->getPrediction($week);
        
        $total_matches = FootballMatch::count();
        $played_matches = FootballMatch::where('is_played', true)->count();
        $is_league_finished = $total_matches > 0 && $total_matches === $played_matches;

        $current_week = $this->getCurrentWeek();

        return [
            'table' => $table,
            'selected_week_matches' => $selected_week_matches,
            'all_matches' => $all_matches,
            'prediction' => $prediction,
            'current_week' => $current_week,
            'is_league_finished' => $is_league_finished
        ];
    }

    /**
     * Starts new season with existing teams
     */
    public function initializeLeague(): void
    {
        FootballMatch::truncate();

        $this->ajaxDispatch(new UpdateTeam([
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
        ]));

        $team_count = Team::count();

        if ($team_count !== 4) {
            throw new \RuntimeException(
                "Exactly 4 teams required for fixture generation, found {$team_count} teams."
            );
        }

        $this->generateFixtures();
    }

    /**
     * Checks if the league is finished
     * 
     * @return bool Is league finished?
     */
    public function isLeagueFinished(): bool
    {
        $total_matches = FootballMatch::count();
        $played_matches = FootballMatch::where('is_played', true)->count();

        return $total_matches > 0 && $played_matches >= $total_matches;
    }

    /**
     * Predicts league winner using advanced AI algorithm
     * 
     * @return array Prediction results
     */
    public function predictLeagueWinner(): array
    {
        $table = $this->getLeagueTable();
        $current_week = $this->getCurrentWeek();
        $total_matches = FootballMatch::count();
        $played_matches = FootballMatch::where('is_played', true)->count();
        $remaining_matches = $total_matches - $played_matches;

        if ($remaining_matches == 0 || ($total_matches > 0 && $played_matches >= $total_matches)) {
            $leader = $table->first();
            if (!$leader) {
                return [
                    'winner' => null,
                    'probability' => 0,
                    'final' => false,
                    'message' => 'League not started yet',
                    'show_prediction' => false
                ];
            }
         
            return [
                'winner' => $leader,
                'probability' => 100,
                'final' => true,
                'message' => 'League completed! Champion: ' . $leader->name,
                'show_prediction' => true,
                'season_finished' => true
            ];
        }

        if ($current_week > 4) {
            $leader = $table->first();
            $second_place = $table->get(1);

            if (!$leader) {
                return [
                    'winner' => null,
                    'probability' => 0,
                    'final' => false,
                    'message' => 'League not started yet',
                    'show_prediction' => false
                ];
            }

            $points_difference = $leader->points - ($second_place ? $second_place->points : 0);
            $max_possible_points_for_second = $remaining_matches * 3;

            if ($points_difference > $max_possible_points_for_second) {
                $probability = 95;
                $message = $leader->name . ' is in a very strong position for the championship!';
            } else {
                $probability = max(55, 85 - (($max_possible_points_for_second - $points_difference) * 5));
                $message = 'Championship race is still ongoing!';
            }

            return [
                'winner' => $leader,
                'probability' => min(95, $probability),
                'final' => false,
                'message' => $message,
                'show_prediction' => true,
                'leader' => $leader->name,
                'season_finished' => false
            ];
        }

        return [
            'winner' => null,
            'probability' => 0,
            'final' => false,
            'message' => 'AI will make predictions after week 4...',
            'show_prediction' => false,
            'season_finished' => false
        ];
    }

    /**
     * Simulate all matches
     * 
     * @return bool Success status
     */
    public function simulateAllMatches(): bool
    {
        $unplayed_matches = FootballMatch::where('is_played', false)->get();

        foreach ($unplayed_matches as $match) {
            $this->simulateMatch($match);
        }
        
        return true;
    }

    /**
     * Simulates match according to Premier League rules
     * 
     * @param FootballMatch $match Match object
     */
    public function simulateMatch(FootballMatch $match): void
    {
        if ($match->is_played) {
            return;
        }

        $home_team = $match->homeTeam;
        $away_team = $match->awayTeam;

        $home_goals = $this->calculateGoals($home_team->strength, $away_team->strength, true);
        $away_goals = $this->calculateGoals($away_team->strength, $home_team->strength, false);

        $this->ajaxDispatch(new UpdateMatch(
            [
                'home_score' => $home_goals,
                'away_score' => $away_goals,
                'is_played' => true,
                'played_at' => Carbon::now(),
            ],
            $match
        ));

        $this->updateStats($home_team);
        $this->updateStats($away_team);
    }

    public function updateStats(Team $team): void
    {
        // Get played matches
        $home_matches = $team->homeMatches()
            ->where('is_played', true)
            ->get();

        $away_matches = $team->awayMatches()
            ->where('is_played', true)
            ->get();
        
        // Initialize stat variables
        $match_stats = [
            'played' => $home_matches->count() + $away_matches->count(),
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
        ];

        // Analyze home matches
        foreach ($home_matches as $match) {
            $match_stats['goals_for'] += $match->home_score;
            $match_stats['goals_against'] += $match->away_score;

            // Evaluate match result
            $match_result = $this->evaluateMatchResult(
                $match->home_score,
                $match->away_score,
                true // home team
            );

            $match_stats[$match_result]++;
        }

        // Analyze away matches
        foreach ($away_matches as $match) {
            $match_stats['goals_for'] += $match->away_score;
            $match_stats['goals_against'] += $match->home_score;

            // Evaluate match result
            $match_result = $this->evaluateMatchResult(
                $match->away_score,
                $match->home_score,
                false // away team
            );

            $match_stats[$match_result]++;
        }

        // Calculate league points
        $total_points = ($match_stats['won'] * 3) + ($match_stats['drawn'] * 1);

        $this->ajaxDispatch(new UpdateTeam(
            [
                'played' => $match_stats['played'],
                'won' => $match_stats['won'],
                'drawn' => $match_stats['drawn'],
                'lost' => $match_stats['lost'],
                'goals_for' => $match_stats['goals_for'],
                'goals_against' => $match_stats['goals_against'],
                'goal_difference' => $match_stats['goals_for'] - $match_stats['goals_against'],
                'points' => $total_points,
            ],
            $team
        ));
    }

    /**
     * Evaluates match result - According to Premier League rules
     * 
     * @param int $team_goals Goals scored by team
     * @param int $opponent_goals Goals scored by opponent
     * @param bool $isHome_team Is home team?
     * @return string Match result ('won', 'drawn', 'lost')
     */
    private function evaluateMatchResult(int $team_goals, int $opponent_goals, bool $isHome_team): string
    {
        if ($team_goals > $opponent_goals) {
            return 'won';
        }

        if ($team_goals === $opponent_goals) {
            return 'drawn';
        }

        return 'lost';
    }

    /**
     * Simulate week
     *
     * @param int $week Week number
     * @return Collection Simulated matches
     */
    public function simulateWeek(int $week): Collection
    {
        $matches = FootballMatch::where('week', $week)
            ->where('is_played', false)
            ->with(['homeTeam', 'awayTeam'])
            ->get();

        foreach ($matches as $match) {
            $this->simulateMatch($match);
        }

        return $matches;
    }

    /**
     * Sorts teams according to Premier League ranking rules
     * 
     * @param Collection $teams Team statistics
     * @param int $up_to_week Up to which week to calculate
     * @return Collection Sorted teams
     */
    private function sortByPremierLeagueRules(Collection $teams, int $up_to_week): Collection
    {
        return $teams->sort(function ($a, $b) use ($up_to_week) {
            if ($a->points !== $b->points) {
                return $b->points <=> $a->points;
            }
         
            if ($a->goal_difference !== $b->goal_difference) {
                return $b->goal_difference <=> $a->goal_difference;
            }
         
            if ($a->goals_for !== $b->goals_for) {
                return $b->goals_for <=> $a->goals_for;
            }
         
            $head_to_head = $this->calculateHeadToHead($a->id, $b->id, $up_to_week);
            if ($head_to_head !== 0) {
                return $head_to_head;
            }
         
            return strcmp($a->name, $b->name);
        })->values();
    }
}
