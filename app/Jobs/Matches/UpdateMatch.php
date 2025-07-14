<?php

namespace App\Jobs\Matches;

use App\Models\FootballMatch;

class UpdateMatch
{
    /**
     * The data to update the match
     *
     * @var array
     */
    public $data;

    /**
     * The match instance to update
     *
     * @var FootballMatch|null
     */
    public $match;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, $match = null)
    {
        $this->data = $data;
        $this->match = $match;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->match) {
            $this->match->update($this->data);
        } else {
            FootballMatch::query()->update($this->data);
        }
    }
}
