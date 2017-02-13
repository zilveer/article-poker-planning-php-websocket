<?php

namespace App;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use App\Topic\TeamsTopic;
use App\Topic\TeamTopic;

class PokerPlanningWebsocketProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app->topic('teams', function ($topicPattern) {
            return new TeamsTopic($topicPattern);
        });

        $app
            ->topic('teams/{teamId}', function ($topicPattern, $arguments) {
                $teamId = intval($arguments['teamId']);

                return new TeamTopic($topicPattern, $teamId);
            })
            ->assert('teamId', '\d+')
        ;
    }
}
