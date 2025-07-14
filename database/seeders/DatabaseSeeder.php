<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 * 
 * Runs all necessary seeds for Premier League simulation.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seeds the application's database
     */
    public function run(): void
    {        
        // Add Premier League teams
        $this->call([
            TeamSeeder::class,
        ]);
    }
}
