<?php

namespace App\Service;

use App\Entity\Team;

class PokerPlanning
{
    /**
     * Check is a vote is in Fibonacci sequence.
     *
     * @param int $vote
     *
     * @return boolean
     */
    public function isVoteFibonacci($vote)
    {
        return in_array($vote, [1, 2, 3, 5, 8, 13, 21, 34]);
    }

    /**
     * Check whether all users voted.
     *
     * @param Team $team
     *
     * @return boolean
     */
    public function hasTeamVoted(Team $team)
    {
        foreach ($team->getUsers() as $user) {
            if (null === $user->getVote()) {
                return false;
            }
        }

        return true;
    }
}
