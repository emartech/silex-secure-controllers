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

    /**
     * @var string
     */
    private $environment;


    public function __construct(Provider $delegate, RequestSecurity $requestSecurity, string $environment)
    {
        $this->delegate = $delegate;
        $this->requestSecurity = $requestSecurity;
        $this->environment = $environment;
    }

    public function connect(Application $app): ControllerCollection
    {
        $controllers = $app['controllers_factory'];
        $this->delegate->setupActions(new Collection($app, $controllers, $this->requestSecurity, $this->environment));
        return $controllers;
    }
}
