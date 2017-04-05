<?php

namespace Emartech\Silex\SecureController;

use LogicException;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

class Collection
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var ControllerCollection
     */
    private $collection;

    /**
     * @var RequestSecurity
     */
    private $requestSecurity;

    /**
     * @var string
     */
    private $environment;


    public static function createRoot(Application $application, RequestSecurity $requestSecurity, $environment)
    {
        return new self($application, $application['controllers'], $requestSecurity, $environment);
    }

    public function __construct(Application $application, ControllerCollection $collection, RequestSecurity $requestSecurity, $environment)
    {
        $this->application = $application;
        $this->collection = $collection;
        $this->requestSecurity = $requestSecurity;
        $this->environment = $environment;
    }

    public function mount($prefix, $controllers)
    {
        if ($controllers instanceof Provider) {
            $wrapped = new ProviderWrapper($controllers, $this->requestSecurity, $this->environment);
            $controllers = $wrapped->connect($this->application);
        } else if ($controllers instanceof ControllerProviderInterface || $controllers instanceof ControllerCollection) {
            throw new LogicException("Please use SecureControllerProvider instances!");
        } else if (is_callable($controllers)) {
            $collection = $this->application['controllers_factory'];
            call_user_func($controllers, new self($this->application, $collection, $this->requestSecurity, $this->environment));
            $controllers = $collection;
        }

        $this->collection->mount($prefix, $controllers);
    }

    public function get($pattern, $to = null, $auth = null, $forceScheme = null)
    {
        return $this->match($pattern, $to, $auth, $forceScheme)->method('GET');
    }

    public function post($pattern, $to = null, $auth = null, $forceScheme = null)
    {
        return $this->match($pattern, $to, $auth, $forceScheme)->method('POST');
    }

    public function put($pattern, $to = null, $auth = null, $forceScheme = null)
    {
        return $this->match($pattern, $to, $auth, $forceScheme)->method('PUT');
    }

    public function delete($pattern, $to = null, $auth = null, $forceScheme = null)
    {
        return $this->match($pattern, $to, $auth, $forceScheme)->method('DELETE');
    }

    public function options($pattern, $to = null, $auth = null, $forceScheme = null)
    {
        return $this->match($pattern, $to, $auth, $forceScheme)->method('OPTIONS');
    }

    public function patch($pattern, $to = null, $auth = null, $forceScheme = null)
    {
        return $this->match($pattern, $to, $auth, $forceScheme)->method('PATCH');
    }

    public function match($pattern, $to, $auth = null, $forceScheme = null)
    {
        if (null === $auth) {
            $auth = $this->escherAuth();
        }

        if (null === $forceScheme) {
            $forceScheme = $this->forceHttps();
        }

        return $this->collection->match($pattern, $to)
            ->before($forceScheme)
            ->before($auth);
    }

    public function noAuth(): callable
    {
        return function () {};
    }

    public function session(): callable
    {
        return function () {
            return $this->requestSecurity->validateSession();
        };
    }

    public function escherAuth(): callable
    {
        return function () {
            return $this->requestSecurity->escherAuthenticate();
        };
    }

    public function jwtAuth(): callable
    {
        return function (Request $request) {
            return $this->requestSecurity->jwtAuthenticate($request);
        };
    }

    private function forceHttps(): callable
    {
        return function (Request $request) {
            if ('development' === $this->environment || 'testing' === $this->environment) {
                return null;
            }
            return $this->requestSecurity->forceHttps($request);
        };
    }

    public function allowHttp()
    {
        return function () {};
    }
}
