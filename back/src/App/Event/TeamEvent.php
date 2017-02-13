<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;
use App\Entity\Team;

class TeamEvent extends Event
{
    /**
     * A team has been created.
     * The listener receives an instance of TeamEvent.
     *
     * @var string
     */
    const CREATED = 'event.team.created';

    /**
     * @var Team
     */
    private $team;

    /**
     * @param Team $team
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * @return Team
     */
    public function getTeam()
    {
        return $this->team;
    }
}
