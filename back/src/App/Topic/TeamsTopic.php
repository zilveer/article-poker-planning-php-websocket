<?php

namespace App\Topic;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Sandstone\Websocket\Topic;
use App\Event\TeamEvent;

class TeamsTopic extends Topic implements EventSubscriberInterface
{
    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TeamEvent::CREATED => 'onTeamCreated',
        ];
    }

    /**
     * @param TeamEvent $event
     */
    public function onTeamCreated(TeamEvent $event)
    {
        $this->broadcast([
            'type' => 'team_created',
            'team' => $event->getTeam(),
        ]);
    }
}
