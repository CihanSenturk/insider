<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    /**
     * Points per win
     */
    private const POINTS_PER_WIN = 3;

    /**
     * Points per draw
     */
    private const POINTS_PER_DRAW = 1;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'strength',
        'played',
        'won',
        'drawn',
        'lost',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
    ];

    /**
     * Data type casts
     */
    protected $casts = [
        'strength' => 'integer',
        'played' => 'integer',
        'won' => 'integer',
        'drawn' => 'integer',
        'lost' => 'integer',
        'goals_for' => 'integer',
        'goals_against' => 'integer',
        'goal_difference' => 'integer',
        'points' => 'integer',
    ];

    /**
     * Home matches - One-to-Many relationship
     */
    public function homeMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'home_team_id');
    }

    /**
     * Away matches - One-to-Many relationship
     */
    public function awayMatches(): HasMany
    {
        return $this->hasMany(FootballMatch::class, 'away_team_id');
    }

    /**
     * Combines all matches (home + away)
     * 
     * @return Collection All team matches
     */
    public function getAllMatches(): Collection
    {
        return $this->homeMatches->merge($this->awayMatches);
    }

    /**
     * Calculates team's current form
     * 
     * @param int $last_matches Last how many matches?
     * @return float
     */
    public function getFormScore(int $last_matches = 3): float
    {
        $recent_matches = $this->getAllMatches()
            ->where('is_played', true)
            ->sortByDesc('id')
            ->take($last_matches);

        if ($recent_matches->isEmpty()) {
            return 0.5;
        }

        $form_points = 0;
        $max_possible_points = $recent_matches->count() * self::POINTS_PER_WIN;

        foreach ($recent_matches as $match) {
            $is_home_team = $match->home_team_id === $this->id;
            $team_goals = $is_home_team ? $match->home_score : $match->away_score;
            $opponent_goals = $is_home_team ? $match->away_score : $match->home_score;

            if ($team_goals > $opponent_goals) {
                $form_points += self::POINTS_PER_WIN;
            } elseif ($team_goals === $opponent_goals) {
                $form_points += self::POINTS_PER_DRAW;
            }
        }

        return $form_points / $max_possible_points;
    }
}
