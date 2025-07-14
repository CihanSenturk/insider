<?php

return [
    // League status
    'status' => [
        'league_finished' => 'League Completed!',
        'weeks_completed' => ':current / :total Weeks Completed',
        'league_active' => 'League in Progress',
        'current_week' => 'Week :current / :total',
    ],
    
    // Buttons and actions
    'buttons' => [
        'start_league' => 'Start League',
        'new_season' => 'Start New Season',
        'play_week' => 'Play Week (:week)',
        'play_all' => 'Play All Matches',
        'reset_league' => 'Reset League',
    ],
    
    // League table
    'table' => [
        'title' => 'League Table',
        'teams' => 'teams',
        'position' => 'Pos',
        'team' => 'Team',
        'played' => 'P',
        'won' => 'W',
        'drawn' => 'D',
        'lost' => 'L',
        'goals_for' => 'GF',
        'goals_against' => 'GA',
        'goal_difference' => 'GD',
        'points' => 'Pts',
        'leader' => 'Leader',
        'legend_title' => 'Legend',
        'legend_desc' => 'P=Played, W=Won, D=Drawn, L=Lost, GF=Goals For, GA=Goals Against, GD=Goal Difference, Pts=Points',
    ],
    
    // Matches
    'matches_list' => [
        'title' => 'Matches & Fixtures',
        'week_title' => 'Week :week',
        'week_matches' => 'Week :week Matches',
        'match_count' => ':count Matches',
        'matches' => 'matches',
        'vs' => 'vs',
        'not_played' => 'Not Played',
        'first_leg' => 'First Leg',
        'second_leg' => 'Second Leg',
        'no_matches' => 'No matches found for this week.',
        'home_win' => 'Home team won',
        'away_win' => 'Away team won',
        'draw' => 'Draw',
        'final' => 'Final',
        'played_info' => 'matches played',
    ],
    
    // Prediction
    'prediction_list' => [
        'title' => 'Championship Prediction',
        'favorite' => 'Favorite',
        'probability' => 'Probability',
        'week_4_early' => 'Early prediction after week 4',
        'week_5_updated' => 'Updated prediction after week 5',
        'final_standings' => 'Final season standings',
        'week_prediction' => 'Week prediction',
        'not_available' => 'Prediction available after week 4',
    ],
    
    // Flash messages
    'messages' => [
        'league_started' => 'League started successfully! 4 teams, 6 weeks fixture created.',
        'league_initialized' => 'League initialized successfully! Ready to play.',
        'week_played' => 'Week :week completed! :matches matches played.',
        'week_simulated' => 'Week :week completed successfully!',
        'no_remaining' => 'All matches already completed!',
        'match_updated' => 'Match result updated successfully!',
        'invalid_score' => 'Invalid score! Score must be 0-9.',
        'season_ended' => 'Season ended: :current / :total weeks',
        'league_completed_status' => 'League completed!',
        'fixture_info' => 'and 6-week fixture will be created',
        'new_season_info' => 'New fixture and clean statistics',
        'control_info' => 'League control and match simulation',
        'league_reset_success' => 'League reset successfully! Ready for new season.',
        'tip' => 'Tip',
        'week_navigation_tip' => 'Click on week tabs to view different weeks.',
        'played_info' => 'matches played',
        'initialization_error' => 'Failed to initialize league: :error',
        'simulation_error' => 'Failed to simulate: :error',
        'week_data_loaded' => 'Week :week data loaded successfully.',
        'week_data_error' => 'Error loading week data.',
    ],
    
    // Week selector
    'week_selector' => [
        'title' => 'Week Navigation',
        'description' => 'Click on a week to view matches and table for that period.',
        'completed' => 'Completed',
        'in_progress' => 'In Progress', 
        'pending' => 'Pending',
    ],
    
    // Controller messages
    'controller' => [
        'week_completed' => 'Week :week matches simulated! League completed! Champion: :champion',
        'week_simulated_success' => 'Week :week matches simulated!',
        'all_matches_completed' => 'All remaining :count matches simulated! League completed! Champion: :champion',
        'league_reset_error' => 'An error occurred while resetting league: :error',
        'invalid_week' => 'Invalid week number. Week must be between 1-6.',
        'status_check_failed' => 'Status check failed: :error',
        'week_data_error' => 'An error occurred while loading week data: :error',
    ],

    // Additional controller messages
    'week_simulation_error' => 'An error occurred while simulating week: :error',
    'all_simulation_error' => 'An error occurred while simulating all matches: :error',
    'initialization_failed' => 'Failed to initialize league: :error',
    'error_loading_week' => 'An error occurred while loading week data.',
];
