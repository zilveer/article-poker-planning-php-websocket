<?php

namespace App;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use App\Event\TeamEvent;
use App\Event\UserEvent;
use App\Converter\TeamConverter;
use App\Converter\UserConverter;

class PokerPlanningRestApiProvider implements ServiceProviderInterface
{
    /**
     * {@InheritDoc}
     */
    public function register(Container $app)
    {
        $app['app.converter.team'] = function () use ($app) {
            return new TeamConverter($app['orm.em']->getRepository('App:Team'));
        };

        $app['app.converter.user'] = function () use ($app) {
            return new UserConverter($app['orm.em']->getRepository('App:User'));
        };

        $app->forwardEventsToPushServer([
            TeamEvent::CREATED,
            UserEvent::JOINED,
            UserEvent::VOTED,
        ]);
    }
}
