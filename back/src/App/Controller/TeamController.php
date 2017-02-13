<?php

namespace App\Controller;

use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Alcalyn\SerializableApiResponse\ApiResponse;
use DDesrosiers\SilexAnnotations\Annotations as SLX;
use App\Entity\User;
use App\Entity\Team;
use App\Event\TeamEvent;
use App\Event\UserEvent;

/**
 * @SLX\Controller(prefix="/api")
 */
class TeamController
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
     * Get all teams.
     *
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/teams")
     * )
     *
     * @return ApiResponse
     */
    public function getTeams()
    {
        $teams = $this->container['orm.em']->getRepository('App:Team')->findAll();

        return new ApiResponse($teams, Response::HTTP_OK);
    }

    /**
     * Get all teams.
     *
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/teams/{team}"),
     *      @SLX\Assert(variable="team", regex="\d+"),
     *      @SLX\Convert(variable="team", callback="app.converter.team:convert")
     * )
     *
     * @return ApiResponse
     */
    public function getTeam(Team $team)
    {
        return new ApiResponse($team, Response::HTTP_OK);
    }

    /**
     * Get all teams.
     *
     * @SLX\Route(
     *      @SLX\Request(method="POST", uri="/teams")
     * )
     *
     * @return ApiResponse
     */
    public function postTeam(Request $request)
    {
        $team = $this->container['serializer']->deserialize($request->getContent(), Team::class, 'json');

        $team->setVoteInProgress(true);

        $this->container['orm.em']->persist($team);
        $this->container['orm.em']->flush();

        $this->container['dispatcher']->dispatch(TeamEvent::CREATED, new TeamEvent($team));

        return new ApiResponse($team, Response::HTTP_CREATED);
    }

    /**
     * Make an user joins a team.
     *
     * @SLX\Route(
     *      @SLX\Request(method="PUT", uri="/teams/{team}/users/{user}"),
     *      @SLX\Convert(variable="team", callback="app.converter.team:convert"),
     *      @SLX\Convert(variable="user", callback="app.converter.user:convert")
     * )
     *
     * @param Team $team
     * @param User $user
     *
     * @return ApiResponse
     */
    public function addUser(Team $team, User $user)
    {
        $team->addUser($user);
        $user->setTeam($team);

        $this->container['orm.em']->persist($team);
        $this->container['orm.em']->flush();

        $this->container['dispatcher']->dispatch(UserEvent::JOINED, new UserEvent($user));

        return new ApiResponse($team, Response::HTTP_OK);
    }
}
