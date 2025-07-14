<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FootballMatch extends Model
{
    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'week',
        'is_played',
        'played_at',
    ];

    /**
     * Data type casts
     */
    protected $casts = [
        'home_team_id' => 'integer',
        'away_team_id' => 'integer',
        'home_score' => 'integer',
        'away_score' => 'integer',
        'week' => 'integer',
        'is_played' => 'boolean',
        'played_at' => 'datetime',
    ];

    /**
     * Default values
     */
    protected $attributes = [
        'is_played' => false,
    ];

    /**
     * Home team - Many-to-One relationship
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Away team - Many-to-One relationship
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Returns match result as formatted string
     * 
     * @return string Match result (e.g., "3-1" or "Not Played")
     */
    public function getResultAttribute(): string
    {
        if (!$this->is_played) {
            return 'Not Played';
        }

        return "{$this->home_score} - {$this->away_score}";
    }

    /**
     * Returns the winner team of the match
     * 
     * @return Team|null Winner team or null (in case of draw)
     */
    public function getWinnerAttribute(): ?Team
    {
        if (!$this->is_played) {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return $this->homeTeam;
        }

        if ($this->away_score > $this->home_score) {
            return $this->awayTeam;
        }

        return null;
    }

    /**
     * Checks if the match ended in a draw
     * 
     * @return bool Is draw?
     */
    public function isDraw(): bool
    {
        return $this->is_played && ($this->home_score === $this->away_score);
    }

    /**
     * Returns total goals in the match
     * 
     * @return int Total goals
     */
    public function getTotalGoals(): int
    {
        if (!$this->is_played) {
            return 0;
        }

        return $this->home_score + $this->away_score;
    }

    /**
     * Checks if the match is high-scoring
     * 
     * @param int $threshold Threshold value (default: 3)
     * @return bool Is high-scoring?
     */
    public function isHighScoring(int $threshold = 3): bool
    {
        return $this->getTotalGoals() > $threshold;
    }

    /**
     * Checks if a specific team won this match
     * 
     * @param Team|int $team Team model or ID
     * @return bool Did team win?
     */
    public function didTeamWin(Team|int $team): bool
    {
        $teamId = $team instanceof Team ? $team->id : $team;
        $winner = $this->getWinnerAttribute();
        
        return $winner && $winner->id === $teamId;
    }
}
