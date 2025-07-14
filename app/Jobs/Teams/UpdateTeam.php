<?php

namespace App\Jobs\Teams;

use App\Models\Team;

class UpdateTeam
{
    /**
     * The data to update the team
     *
     * @var array
     */
    public $data;

    /**
     * The team instance to update
     *
     * @var Team|null
     */
    public $team;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, $team = null)
    {
        $this->data = $data;
        $this->team = $team;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->team) {
            $this->team->update($this->data);
        } else {
            Team::query()->update($this->data);
        }
    }
}
