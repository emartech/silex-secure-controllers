<?php

namespace Emartech\Silex\SecureController;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class ProviderWrapper implements ControllerProviderInterface
{
    /**
     * @var Provider
     */
    private $delegate;

    /**
     * @var RequestSecurity
     */
    private $requestSecurity;


    public function __construct(Provider $delegate, RequestSecurity $requestSecurity)
    {
        $this->delegate = $delegate;
        $this->requestSecurity = $requestSecurity;
    }

    public function connect(Application $app): ControllerCollection
    {
        $controllers = $app['controllers_factory'];
        $this->delegate->setupActions(new Collection($app, $controllers, $this->requestSecurity));
        return $controllers;
    }
}
