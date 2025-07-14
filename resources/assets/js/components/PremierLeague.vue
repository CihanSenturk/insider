<template>
    <div>
        <!-- League Status - Top Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card status-card text-center">
                    <div class="card-body py-3">
                        <div class="league-status text-dark">
                            <!-- League Not Started -->
                            <div v-if="!hasData" class="row align-items-center">
                                <div class="col-md-4">
                                    <h5 class="mb-0 text-dark">
                                        <i class="fas fa-pause-circle me-2 text-warning"></i>
                                        {{ translations.status && translations.status.not_started ? translations.status.not_started : 'League Not Started' }}
                                    </h5>
                                </div>
                                <div class="col-md-4 text-center">
                                    <button @click="initializeLeague" :disabled="loading" class="btn btn-success px-4">
                                        <i class="fas fa-spinner fa-spin me-2" v-if="loading"></i>
                                        <i class="fas fa-play me-2" v-else></i>
                                        {{ loading ? (translations.vue && translations.vue.processing ? translations.vue.processing : 'Processing...') : (translations.buttons && translations.buttons.start_league ? translations.buttons.start_league : 'Start League') }}
                                    </button>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-secondary text-white px-3 py-2">
                                        <i class="fas fa-users me-1"></i>
                                        {{ teamsReadyText }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- League Completed -->
                            <div v-else-if="isLeagueFinished" class="row align-items-center">
                                <div class="col-md-4">
                                    <h5 class="mb-0 text-dark">
                                        <i class="fas fa-trophy me-2 text-warning"></i>
                                        {{ translations.status && translations.status.league_finished ? translations.status.league_finished : 'League Finished' }}
                                    </h5>
                                </div>
                                <div class="col-md-4 text-center">
                                    <button @click="initializeLeague" :disabled="loading" class="btn btn-success px-4">
                                        <i class="fas fa-spinner fa-spin me-2" v-if="loading"></i>
                                        <i class="fas fa-plus me-2" v-else></i>
                                        {{ loading ? (translations.vue && translations.vue.processing ? translations.vue.processing : 'Processing...') : (translations.buttons && translations.buttons.new_season ? translations.buttons.new_season : 'New Season') }}
                                    </button>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge bg-success text-white px-3 py-2">
                                        <i class="fas fa-flag-checkered me-1"></i>
                                        {{ weeksCompletedText }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- League Active -->
                            <div v-else class="row align-items-center">
                                <div class="col-md-3">
                                    <h5 class="mb-0 text-dark">
                                        <i class="fas fa-play-circle me-2 text-success"></i>
                                        {{ translations.status && translations.status.league_active ? translations.status.league_active : 'League Active' }}
                                    </h5>
                                </div>
                                <div class="col-md-6 text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button v-if="currentWeek <= 6" @click="simulateWeek" :disabled="loading" class="btn btn-primary btn-sm">
                                            <i class="fas fa-spinner fa-spin me-1" v-if="loading"></i>
                                            <i class="fas fa-forward me-1" v-else></i>
                                            {{ loading ? (translations.vue && translations.vue.processing ? translations.vue.processing : 'Processing...') : playWeekText }}
                                        </button>
                                        
                                        <button @click="simulateAll" :disabled="loading" class="btn btn-warning btn-sm">
                                            <i class="fas fa-spinner fa-spin me-1" v-if="loading"></i>
                                            <i class="fas fa-fast-forward me-1" v-else></i>
                                            {{ loading ? (translations.vue && translations.vue.processing ? translations.vue.processing : 'Processing...') : (translations.buttons && translations.buttons.play_all ? translations.buttons.play_all : 'Play All') }}
                                        </button>
                                        
                                        <button @click="initializeLeague" :disabled="loading" class="btn btn-danger btn-sm">
                                            <i class="fas fa-spinner fa-spin me-1" v-if="loading"></i>
                                            <i class="fas fa-redo me-1" v-else></i>
                                            {{ loading ? (translations.vue && translations.vue.processing ? translations.vue.processing : 'Processing...') : (translations.buttons && translations.buttons.reset_league ? translations.buttons.reset_league : 'Reset League') }}
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <span class="badge bg-primary text-white px-3 py-2">
                                        <i class="fas fa-calendar-week me-1"></i>
                                        {{ currentWeekText }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Week Selector -->
        <div v-if="hasData && Object.keys(allMatches).length > 0" class="card mb-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ translations.week_selector && translations.week_selector.title ? translations.week_selector.title : 'Week Selector' }}
                        </h5>
                        <small class="text-muted">{{ translations.week_selector && translations.week_selector.description ? translations.week_selector.description : 'Select a week to view' }}</small>
                    </div>
                    <div class="col-md-8">
                        <ul class="nav nav-pills justify-content-center">
                            <li v-for="(weekMatches, week) in allMatches" :key="week" class="nav-item">
                                <button @click="changeWeek(week)" :disabled="loading"                                :class="['nav-link week-selector-btn', 
                                        selectedWeek == week ? 'active' : '',
                                        weekMatches && weekMatches.filter(m => m && m.is_played).length > 0 ? 'has-results' : 'pending',
                                        weekMatches && weekMatches.filter(m => m && !m.is_played).length === 0 && weekMatches.length > 0 ? 'completed' : '']">
                                    <i class="fas fa-calendar-week me-1"></i>
                                    <span class="week-number">{{ week }}</span>
                                    <small class="d-block status-text">
                                        <span v-if="weekMatches && weekMatches.filter(m => m && !m.is_played).length === 0 && weekMatches.length > 0">
                                            <i class="fas fa-check-circle me-1"></i>{{ translations.week_selector && translations.week_selector.completed ? translations.week_selector.completed : 'Completed' }}
                                        </span>
                                        <span v-else-if="weekMatches && weekMatches.filter(m => m && m.is_played).length > 0">
                                            <i class="fas fa-clock me-1"></i>{{ translations.week_selector && translations.week_selector.in_progress ? translations.week_selector.in_progress : 'In Progress' }}
                                        </span>
                                        <span v-else>
                                            <i class="fas fa-hourglass-start me-1"></i>{{ translations.week_selector && translations.week_selector.pending ? translations.week_selector.pending : 'Pending' }}
                                        </span>
                                    </small>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- League Table -->
            <div class="col-lg-6 mb-4">
                <div v-if="hasData && table.length > 0" class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-trophy me-2"></i>
                            {{ translations.table.title }}
                        </h5>
                        <span class="badge bg-primary">
                            {{ table.length }} {{ translations.table.teams }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 50px;">{{ translations.table.position }}</th>
                                        <th>{{ translations.table.team }}</th>
                                        <th class="text-center">{{ translations.table.played }}</th>
                                        <th class="text-center">{{ translations.table.won }}</th>
                                        <th class="text-center">{{ translations.table.drawn }}</th>
                                        <th class="text-center">{{ translations.table.lost }}</th>
                                        <th class="text-center">{{ translations.table.goals_for }}</th>
                                        <th class="text-center">{{ translations.table.goals_against }}</th>
                                        <th class="text-center">{{ translations.table.goal_difference }}</th>
                                        <th class="text-center"><strong>{{ translations.table.points }}</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(team, index) in table" :key="team.id" :class="index === 0 ? 'table-warning' : ''">
                                        <td class="text-center">
                                            <i v-if="index === 0 && team.played === 6" class="fas fa-crown text-warning"></i>
                                            <span v-else>{{ index + 1 }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ team && team.name ? team.name : 'Unknown Team' }}</strong>
                                            <span v-if="index === 0 && team && team.played === 6" class="badge bg-warning text-dark ms-2">{{ translations.table && translations.table.leader ? translations.table.leader : 'Leader' }}</span>
                                        </td>
                                        <td class="text-center">{{ team && typeof team.played !== 'undefined' ? team.played : 0 }}</td>
                                        <td class="text-center text-success">{{ team && typeof team.won !== 'undefined' ? team.won : 0 }}</td>
                                        <td class="text-center text-warning">{{ team && typeof team.drawn !== 'undefined' ? team.drawn : 0 }}</td>
                                        <td class="text-center text-danger">{{ team && typeof team.lost !== 'undefined' ? team.lost : 0 }}</td>
                                        <td class="text-center">{{ team && typeof team.goals_for !== 'undefined' ? team.goals_for : 0 }}</td>
                                        <td class="text-center">{{ team && typeof team.goals_against !== 'undefined' ? team.goals_against : 0 }}</td>
                                        <td class="text-center">
                                            <span v-if="team && team.goal_difference && team.goal_difference > 0" class="text-success">+{{ team.goal_difference }}</span>
                                            <span v-else-if="team && team.goal_difference && team.goal_difference < 0" class="text-danger">{{ team.goal_difference }}</span>
                                            <span v-else class="text-muted">0</span>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-primary">{{ team && typeof team.points !== 'undefined' ? team.points : 0 }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>
                            <strong>{{ translations.table.legend_title }}:</strong> {{ translations.table.legend_desc }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Match Results -->
            <div class="col-lg-6 mb-4">
                <div v-if="hasData && selectedWeekMatches.length > 0" class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-futbol me-2"></i>
                                {{ weekMatchesText }}
                            </h5>
                            <span class="badge bg-info">
                                {{ matchCountText }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body p-3">
                        <div class="matches-list">
                            <div v-for="match in selectedWeekMatches" :key="match.id" 
                                 :class="['match-card mb-2 p-3 border rounded', match.is_played ? 'bg-light played' : 'bg-white']">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <div class="teams">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="team-name">{{ getTeamName(match, 'home') }}</span>
                                                <span v-if="match && match.is_played" class="badge bg-primary">{{ match.home_score || 0 }}</span>
                                                <span v-else class="text-muted">-</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="team-name">{{ getTeamName(match, 'away') }}</span>
                                                <span v-if="match && match.is_played" class="badge bg-secondary">{{ match.away_score || 0 }}</span>
                                                <span v-else class="text-muted">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>{{ translations.messages.tip }}:</strong> {{ translations.messages.week_navigation_tip }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Championship Prediction -->
        <div class="row mb-5">
            <div class="col-12">
                <div v-if="prediction && prediction.show_prediction" class="card prediction-sidebar">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-crystal-ball me-2"></i>
                            {{ translations.prediction_list.title }}
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h4>{{ prediction.message || '' }}</h4>
                        <div v-if="prediction && prediction.winner && prediction.winner.name">
                            <p>{{ translations.prediction_list && translations.prediction_list.favorite ? translations.prediction_list.favorite : 'Favorite' }}: <strong>{{ prediction.winner.name }}</strong></p>
                            <p>{{ translations.prediction_list && translations.prediction_list.probability ? translations.prediction_list.probability : 'Probability' }}: <strong>%{{ prediction.probability || 0 }}</strong></p>
                        </div>
                    </div>
                </div>
                <div v-else-if="prediction && prediction.week < 4" class="card prediction-sidebar">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-crystal-ball me-2"></i>
                            {{ translations.prediction_list.title }}
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted">{{ translations.prediction_list.not_available }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'PremierLeague',
    
    props: {
        initialData: {
            type: Object,
            default: () => ({})
        }
    },
    
    data() {
        return {
            hasData: false,
            table: [],
            allMatches: {},
            selectedWeekMatches: [],
            currentWeek: 1,
            selectedWeek: 1,
            prediction: null,
            isLeagueFinished: false,
            loading: false,
            routes: {},
            translations: {},
        };
    },
    
    computed: {
        safeTranslations() {
            return this.translations || {};
        },
        teamsReadyText() {
            return (this.safeTranslations.status && this.safeTranslations.status.teams_ready) 
                ? this.safeTranslations.status.teams_ready.replace(':count', '4') : 'Teams Ready: 4';
        },
        weeksCompletedText() {
            return (this.safeTranslations.status && this.safeTranslations.status.weeks_completed) 
                ? this.safeTranslations.status.weeks_completed.replace(':current', '6').replace(':total', '6') : 'Weeks: 6/6';
        },
        currentWeekText() {
            return (this.safeTranslations.status && this.safeTranslations.status.current_week) 
                ? this.safeTranslations.status.current_week.replace(':current', this.currentWeek).replace(':total', '6') : `Week: ${this.currentWeek}/6`;
        },
        playWeekText() {
            return (this.safeTranslations.buttons && this.safeTranslations.buttons.play_week) 
                ? this.safeTranslations.buttons.play_week.replace(':week', this.currentWeek) : `Play Week ${this.currentWeek}`;
        },
        weekMatchesText() {
            return (this.safeTranslations.matches_list && this.safeTranslations.matches_list.week_matches) 
                ? this.safeTranslations.matches_list.week_matches.replace(':week', this.selectedWeek) : `Week ${this.selectedWeek} Matches`;
        },
        matchCountText() {
            return (this.safeTranslations.matches_list && this.safeTranslations.matches_list.match_count) 
                ? this.safeTranslations.matches_list.match_count.replace(':count', this.selectedWeekMatches.length) : `${this.selectedWeekMatches.length} Matches`;
        }
    },
    
    mounted() {
        // Initialize data from props
        if (this.initialData) {
            Object.assign(this.$data, this.initialData);
        }
    },
    
    methods: {
        getTeamName(match, type) {
            if (!match) return 'Unknown Team';
            
            if (type === 'home') {
                return match.home_team?.name || match.homeTeam?.name || 'Home Team';
            } else if (type === 'away') {
                return match.away_team?.name || match.awayTeam?.name || 'Away Team';
            }
            
            return 'Unknown Team';
        },
        
        async initializeLeague() {
            this.loading = true;
            
            try {
                const response = await window.axios.post(this.routes.initialize);
                
                if (response.data.success) {
                    // Redirect to refresh the page
                    this.showSuccessMessage(response.data.message);

                    window.location.href = response.data.redirect;
                } else {
                    this.showErrorMessage(response.data.message);
                }
            } catch (error) {
                this.handleError(error);
            } finally {
                this.loading = false;
            }
        },
        
        async simulateWeek() {
            this.loading = true;
            
            try {
                const response = await window.axios.post(this.routes.simulate_week);
                
                if (response.data.success) {
                    // Redirect to refresh the page
                    window.location.href = response.data.redirect;

                    this.showSuccessMessage(response.data.message);
                } else {
                    this.showErrorMessage(response.data.message);
                }
            } catch (error) {
                this.handleError(error);
            } finally {
                this.loading = false;
            }
        },
        
        async simulateAll() {
            this.loading = true;
            
            try {
                const response = await window.axios.post(this.routes.simulate_all);
                
                if (response.data.success) {
                    this.showSuccessMessage(response.data.message);
                    // Redirect to refresh the page
                    window.location.href = response.data.redirect;
                } else {
                    this.showErrorMessage(response.data.message);
                }
            } catch (error) {
                this.handleError(error);
            } finally {
                this.loading = false;
            }
        },
        
        async changeWeek(week) {
            if (week === this.selectedWeek) return;
            
            this.loading = true;
            
            try {
                const url = this.routes.week_data.replace('WEEK_PLACEHOLDER', week);
                const response = await window.axios.get(url);
                
                if (response.data.success) {
                    this.selectedWeek = response.data.week;
                    this.table = response.data.table;
                    this.selectedWeekMatches = response.data.matches;
                    this.allMatches = response.data.allMatches;
                    this.prediction = response.data.prediction;
                    this.currentWeek = response.data.currentWeek;
                    this.isLeagueFinished = response.data.isLeagueFinished;
                } else {
                    this.showErrorMessage(response.data.message);
                }
            } catch (error) {
                this.handleError(error);
            } finally {
                this.loading = false;
            }
        },
        
        showSuccessMessage(message) {
            this.showFlashMessage(message, 'success');
        },
        
        showErrorMessage(message) {
            this.showFlashMessage(message, 'danger');
        },
        
        showFlashMessage(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show position-fixed';
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 450px; min-width: 350px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            alertDiv.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <strong>${message}</strong>
                    </div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            document.body.appendChild(alertDiv);
        },
        
        handleError(error) {
            console.error('API Error:', error);
            let message = (this.translations.vue && this.translations.vue.error) 
                ? this.translations.vue.error : 'An error occurred';
            
            if (error.response) {
                if (error.response.status >= 500) {
                    message = (this.translations.vue && this.translations.vue.server_error) 
                        ? this.translations.vue.server_error : 'Server error occurred';
                } else if (error.response.data && error.response.data.message) {
                    message = error.response.data.message;
                }
            } else if (error.request) {
                message = (this.translations.vue && this.translations.vue.network_error) 
                    ? this.translations.vue.network_error : 'Network error occurred';
            }
            
            this.showErrorMessage(message);
        }
    }
};
</script>

<style scoped>
.status-card {
    background: linear-gradient(135deg, #a8d8f0 0%, #b8a9dc 100%);
    color: white;
    border: none;
}

.match-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    background: #fafafa;
    transition: all 0.3s ease;
}

.match-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.match-result {
    font-weight: bold;
    font-size: 1.1em;
}

.team-name {
    font-weight: 600;
}

.control-buttons {
    position: sticky;
    bottom: 20px;
    z-index: 1000;
    margin-top: 2rem;
}

.prediction-sidebar {
    background: linear-gradient(135deg, #ffb3ba 0%, #ffdfba 50%, #ffffba 100%);
    border: none;
    color: #444;
}

.week-selector-btn {
    min-width: 80px;
    margin: 0 2px;
    transition: all 0.3s ease;
}

.week-selector-btn.active {
    background-color: #007bff;
    border-color: #007bff;
}

.week-selector-btn.completed {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.week-selector-btn.has-results {
    background-color: #ffc107;
    border-color: #ffc107;
    color: black;
}

.week-selector-btn.pending {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.week-number {
    font-size: 1em;
}

.status-text {
    font-size: 0.7em;
}

.match-card {
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
}

.match-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left-color: #007bff;
}

.match-card.played {
    border-left-color: #28a745;
}

.team-name {
    font-weight: 500;
    font-size: 0.9rem;
}

.matches-list {
    max-height: 400px;
    overflow-y: auto;
}

.matches-list::-webkit-scrollbar {
    width: 6px;
}

.matches-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.matches-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.matches-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
