# 🏆 Premier League Simulation

A modern and professional Premier League football simulation system built with Laravel, Vue.js, and Bootstrap. It offers comprehensive league management, realistic match simulation, and real-time statistics.

---

## 🛠️ Technology Stack

| Layer       | Technology                        |
|-------------|-----------------------------------|
| Backend     | PHP 8.3+, Laravel 11.x            |
| Frontend    | Vue.js 2.7+, Bootstrap 5.3        |
| Database    | SQLite                            |
| Build Tools | Laravel Mix, Webpack              |
| Testing     | PHPUnit                           |

---

## 📋 Requirements

```bash
PHP >= 8.3  
Composer >= 2.0  
Node.js >= 16.x  
NPM >= 8.x  
SQLite 3.x  
```

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd premier_league
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Database Setup

```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed # optional
```

### 6. Compile Frontend Assets

```bash
npm run dev         # For development
npm run production  # For production
npm run watch       # Watch for changes
```

### 7. Start the Application

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000) to access the application.

---

## 🎮 Usage

### Basic Workflow

1. **Start League**  
   Click "Start League" to create teams and fixtures.

2. **Simulate Matches**  
   Click "Play Week" to simulate each week's matches.

3. **View Statistics**  
   Browse the league table and match results.

4. **Navigate Weeks**  
   Use the week tabs to switch between different periods.

5. **Championship Prediction (AI)**  
   View AI-based predictions starting from week 4.

---

## 🧪 Testing

```bash
php artisan test                        # Run all tests
php artisan test --coverage            # With coverage
php artisan test tests/Feature/LeagueServiceTest.php  # Specific test
```

---

## 📄 License

This project is open-source and licensed under the MIT License.
