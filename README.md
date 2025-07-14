# 🏆 Premier League Simulation

A modern and professional Premier League football simulation system built with Laravel, Vue.js, and Bootstrap. Features comprehensive league management, realistic match simulation, and real-time statistics.

## 🛠️ Technology Stack

- **Backend**: PHP 8.3+ | Laravel 11.x
- **Frontend**: Vue.js 2.7+ | Bootstrap 5.3
- **Database**: SQLite
- **Build Tools**: Laravel Mix | Webpack
- **Testing**: PHPUnit

## 📋 Requirements

\`\`\`bash
PHP >= 8.3
Composer >= 2.0
Node.js >= 16.x
NPM >= 8.x
SQLite 3.x
\`\`\`

## 🚀 Installation

### 1. Clone the Repository
\`\`\`bash
git clone <repository-url>
cd premier_league
\`\`\`

### 2. Install PHP Dependencies
\`\`\`bash
composer install
\`\`\`

### 3. Install Node.js Dependencies
\`\`\`bash
npm install
\`\`\`

### 4. Environment Setup
\`\`\`bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
\`\`\`

### 5. Database Setup
\`\`\`bash
# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database (optional)
php artisan db:seed
\`\`\`

### 6. Compile Frontend Assets
\`\`\`bash
# Development build
npm run dev

# Production build
npm run production

# Watch for changes (development)
npm run watch
\`\`\`

### 7. Start the Application
\`\`\`bash
php artisan serve
\`\`\`

## 🎮 Usage

### Basic Workflow
1. **Initialize League**: Click "Start League" to create teams and fixtures
2. **Simulate Matches**: Use "Play Week" buttons to simulate weekly matches
3. **View Statistics**: Browse league table and match results
4. **Navigate Weeks**: Click week tabs to view different periods
5. **Championship Prediction**: View AI predictions from week 4 onwards

## 🧪 Testing

\`\`\`bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test class
php artisan test tests/Feature/LeagueServiceTest.php
\`\`\`
