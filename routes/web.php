<?php

use App\Http\Controllers\LeagueController;
use Illuminate\Support\Facades\Route;

// Main page route
Route::get('/', [LeagueController::class, 'index'])->name('league.index');

// League Management Routes Group
Route::group(['prefix' => 'league', 'as' => 'league.'], function () {    
    Route::post('initialize', [LeagueController::class, 'initialize'])->name('initialize');
    Route::post('reset', [LeagueController::class, 'reset'])->name('reset');
    Route::post('simulate-week', [LeagueController::class, 'simulateWeek'])->name('simulate-week');
    Route::post('simulate-all', [LeagueController::class, 'simulateAll'])->name('simulate-all');
    Route::get('week/{week}/data', [LeagueController::class, 'getWeekData'])->name('week-data');
});
