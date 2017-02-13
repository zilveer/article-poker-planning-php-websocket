<?php

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;
use App\Entity\User;

class UserEvent extends Event
{
    /**
     * An user joined a team.
     * The listener receives an instance of UserEvent.
     *
     * @var string
     */
    const JOINED = 'event.user.joined';

    /**
     * An user voted or changed his vote.
     * The listener receives an instance of UserEvent.
     *
     * @var string
     */
    const VOTED = 'event.user.voted';

    /**
     * @var User
     */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
