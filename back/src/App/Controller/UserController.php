<?php

namespace App\Controller;

use Pimple\Container;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Alcalyn\SerializableApiResponse\ApiResponse;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use App\Entity\User;
use App\Event\UserEvent;

/**
 * @SLX\Controller(prefix="/api")
 */
class UserController
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Creates a new user.
     *
     * @SLX\Route(
     *      @SLX\Request(method="POST", uri="/users")
     * )
     *
     * @return ApiResponse
     */
    public function postUser(Request $request)
    {
        $user = $this->container['serializer']->deserialize($request->getContent(), User::class, 'json');

        $this->container['orm.em']->persist($user);
        $this->container['orm.em']->flush();

        return new ApiResponse($user, Response::HTTP_OK);
    }

    /**
     * Vote. Vote number must be in body, and number in Fibonacci sequence.
     *
     * @SLX\Route(
     *      @SLX\Request(method="POST", uri="/users/{user}/vote"),
     *      @SLX\Convert(variable="user", callback="app.converter.user:convert")
     * )
     *
     * @param Request $request
     * @param User $user
     *
     * @throws BadRequestHttpException If vote is not in Fibonacci sequence.
     *
     * @return ApiResponse
     */
    public function postVote(Request $request, User $user)
    {
        $pokerPlanning = $this->container['app.poker_planning'];
        $vote = intval($request->getContent());
        $team = $user->getTeam();

        if (!$pokerPlanning->isVoteFibonacci($vote)) {
            throw new BadRequestHttpException('Vote must be in Fibonacci sequence.');
        }

        if (!$team->getVoteInProgress()) {
            throw new ConflictHttpException('Cannot vote now, votes are closed.');
        }

        $user->setVote($vote);

        if ($pokerPlanning->hasTeamVoted($team)) {
            $team->setVoteInProgress(false);
        }

        $this->container['orm.em']->persist($user);
        $this->container['orm.em']->flush();

        $this->container['dispatcher']->dispatch(UserEvent::VOTED, new UserEvent($user));

        return new ApiResponse(null, Response::HTTP_NO_CONTENT);
    }
}
