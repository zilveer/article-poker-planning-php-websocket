<?php

namespace App\Topic;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Eole\Sandstone\Websocket\Topic;
use App\Event\UserEvent;

class TeamTopic extends Topic implements EventSubscriberInterface
{
    /**
     * @var int
     */
    private $teamId;

    /**
     * @param string $topicPattern
     * @param int $teamId
     */
    public function __construct($topicPattern, $teamId)
    {
        parent::__construct($topicPattern);

        $this->teamId = $teamId;
    }

    /**
     * {@InheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserEvent::JOINED => 'onUserJoined',
            UserEvent::VOTED => 'onUserVoted',
        ];
    }

    /**
     * @param UserEvent $event
     */
    public function onUserJoined(UserEvent $event)
    {
        if ($event->getUser()->getTeam()->getId() !== $this->teamId) {
            return;
        }

        $this->broadcast([
            'type' => 'user_joined',
            'user' => $event->getUser(),
        ]);
    }

    /**
     * @param UserEvent $event
     */
    public function onUserVoted(UserEvent $event)
    {
        if ($event->getUser()->getTeam()->getId() !== $this->teamId) {
            return;
        }

        $this->broadcast([
            'type' => 'user_voted',
            'user' => $event->getUser(),
        ]);
    }
}
