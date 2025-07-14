@extends('layouts.app')

@section('title', trans('league.title'))

@section('content')
<div id="premier-league-app">
    <premier-league :initial-data="{{ json_encode([
        'hasData'               => $has_data,
        'table'                 => $table,
        'allMatches'            => $all_matches,
        'selectedWeekMatches'   => $selected_week_matches,
        'currentWeek'           => $current_week,
        'selectedWeek'          => $selected_week,
        'prediction'            => $prediction,
        'isLeagueFinished'      => $is_league_finished,
        'routes'                => [
            'initialize'    => route('league.initialize'),
            'simulate_week' => route('league.simulate-week'),
            'simulate_all'  => route('league.simulate-all'),
            'week_data'     => route('league.week-data', ['week' => 'WEEK_PLACEHOLDER']),
            'index'         => route('league.index')
        ],
        'translations' => [
            'vue'               => trans('league.vue'),
            'buttons'           => trans('league.buttons'),
            'confirm'           => trans('league.confirm'),
            'messages'          => trans('league.messages'),
            'status'            => trans('league.status'),
            'week_selector'     => trans('league.week_selector'),
            'table'             => trans('league.table'),
            'matches_list'      => trans('league.matches_list'),
            'prediction_list'   => trans('league.prediction_list')
        ]
    ]) }}"></premier-league>
</div>
@endsection

@push('scripts')
    <script src="{{ mix('js/app.js') }}"></script>
@endpush

@push('styles')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
@endpush