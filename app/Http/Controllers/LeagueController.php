<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\LeagueService;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LeagueController
{
    private $league_service;

    /**
     * Constructor - League service dependency injection
     * 
     * @param LeagueService $league_service League management service
     */
    public function __construct(LeagueService $league_service)
    {
        $this->league_service = $league_service;
    }

    /**
     * Main page view
     * 
     * @return View Main page view
     */
    public function index(): View
    {
        // Get all teams
        $teams = Team::all();
        $has_data = $teams->isNotEmpty();
        
        // Default values
        $table = collect();
        $all_matches = collect();
        $selected_week_matches = collect();
        $current_week = 1;
        $selected_week = 1;
        $prediction = null;
        $is_league_finished = false;

        // If league is initialized, prepare data
        if ($has_data) {
            $current_week = $this->league_service->getCurrentWeek();
            $selected_week = $current_week;

            $table = $this->league_service->getLeagueTableUpToWeek($selected_week);

            // Group matches by week
            $all_matches = FootballMatch::with(['homeTeam', 'awayTeam'])
                ->orderBy('week')
                ->orderBy('id')
                ->get()
                ->groupBy('week');
            
            // Get matches for selected week
            $selected_week_matches = FootballMatch::with(['homeTeam', 'awayTeam'])
                ->where('week', $selected_week)
                ->orderBy('id')
                ->get();

            $prediction = $this->league_service->getPredictionForWeek($selected_week);
            $is_league_finished = $this->league_service->isLeagueFinished();
        }

        return view('league.index', compact('has_data', 'table', 'all_matches', 'selected_week_matches', 'current_week', 'selected_week', 'prediction', 'is_league_finished'));
    }

    /**
     * Initialize league - Creates new season
     * 
     * This method starts a new league season. It clears all existing data,
     * creates 4 teams and prepares random fixtures. Provides different
     * experience with each reset.
     * 
     * @return JsonResponse Success message with redirect to main page
     * @throws \Exception If initialization fails
     */
    public function initialize(): \Illuminate\Http\JsonResponse
    {
        try {
            $this->league_service->initializeLeague();

            return response()->json([
                'success' => true,
                'message' => trans('league.messages.league_initialized'),
                'redirect' => route('league.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('league.messages.initialization_error', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * Gets data up to a specific week
     * 
     * @param int $week Week number
     * @return JsonResponse JSON response
     * @throws \Exception If an error occurs while fetching data
     */
    public function getWeekData(int $week): \Illuminate\Http\JsonResponse
    {
        try {
            $table = $this->league_service->getLeagueTableUpToWeek($week);

            // Group all matches by week
            $all_matches = FootballMatch::with(['homeTeam', 'awayTeam'])
                ->orderBy('week')
                ->orderBy('id')
                ->get()
                ->groupBy('week');
                
            // Get matches for selected week
            $week_matches = $all_matches->get($week, collect());

            // Prediction info (only for past weeks)
            $prediction = null;
            if ($week <= $this->league_service->getCurrentWeek()) {
                $prediction = $this->league_service->getPredictionForWeek($week);
            }

            return response()->json([
                'success' => true,
                'week' => $week,
                'table' => $table->toArray(),
                'matches' => $week_matches->toArray(),
                'allMatches' => $all_matches->toArray(),
                'prediction' => $prediction,
                'currentWeek' => $this->league_service->getCurrentWeek(),
                'isLeagueFinished' => $this->league_service->isLeagueFinished()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('league.controller.week_data_error', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * 
     * This method resets the league and prepares for a clean start.
     * Teams are preserved, only matches are deleted.
     * 
     * @return RedirectResponse
     * @throws \Exception If an error occurs while resetting
     */
    public function reset(): RedirectResponse
    {
        try {
            FootballMatch::truncate();
            
            return redirect()->route('league.index')->with('success', trans('league.messages.league_reset_success'));
        } catch (\Exception $e) {
            return redirect()->route('league.index')->with('error', trans('league.controller.league_reset_error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Week simulation
     * 
     * @return JsonResponse
     * @throws \Exception If an error occurs during simulation
     */
    public function simulateWeek(): \Illuminate\Http\JsonResponse
    {
        try {
            $current_week = $this->league_service->getCurrentWeek();

            if ($current_week > 6) {
                return response()->json([
                    'success' => false,
                    'message' => trans('league.messages.season_finished')
                ], 400);
            }

            $this->league_service->simulateWeek($current_week);

            return response()->json([
                'success' => true,
                'message' => trans('league.messages.week_simulated', ['week' => $current_week]),
                'redirect' => route('league.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('league.messages.simulation_error', ['error' => $e->getMessage()])
            ], 500);
        }
    }

    /**
     * Full season simulation
     *
     * @return JsonResponse
     * @throws \Exception If an error occurs during simulation
     */
    public function simulateAll(): \Illuminate\Http\JsonResponse
    {
        try {
            $this->league_service->simulateAllMatches();

            return response()->json([
                'success' => true,
                'message' => trans('league.messages.season_completed'),
                'redirect' => route('league.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('league.messages.simulation_error', ['error' => $e->getMessage()])
            ], 500);
        }
    }
}
