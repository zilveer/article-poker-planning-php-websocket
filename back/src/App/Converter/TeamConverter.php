<?php

namespace App\Converter;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Repository\TeamRepository;

class TeamConverter
{
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    /**
     * @param TeamRepository $teamRepository
     */
    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function convert($id)
    {
        $team = $this->teamRepository->find($id);

        if (null === $team) {
            throw new NotFoundHttpException('Team not found.');
        }

        return $team;
    }
}
