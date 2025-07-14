<?php

namespace App\Jobs\Matches;

use App\Models\FootballMatch;

class CreateMatch 
{
    /**
     * The data to create the match
     *
     * @var array
     */
    public $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create the match using the provided data
        FootballMatch::create($this->data);
    }
}
