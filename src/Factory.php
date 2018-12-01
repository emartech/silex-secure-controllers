<?php

namespace Emartech\Silex\SecureController;

use Escher\Provider as EscherProvider;
use Psr\Log\LoggerInterface;
use Silex\Application;

class Factory
{
    private $app;
    private $logger;
    private $escherProvider;


    public function __construct(Application $app, LoggerInterface $logger, EscherProvider $provider)
    {
        $this->app = $app;
        $this->logger = $logger;
        $this->escherProvider = $provider;
    }

    public function createCollectionWithBasic(string $environment)
    {
        return $this->createCollection($environment, $this->createBasicSecurity());
    }

    public function createCollectionWithSession(string $environment, SessionValidator $sessionValidator)
    {
        return $this->createCollection($environment, $this->createSessionSecurity($sessionValidator));
    }

    public function createCollectionWithSessionKey(string $environment, string $sessionKey, string $urlKey)
    {
        return $this->createCollectionWithSession($environment, new UrlSessionValidator($sessionKey, $urlKey));
    }

    private function createBasicSecurity(): BasicRequestSecurity
    {
        return new BasicRequestSecurity($this->logger, $this->escherProvider);
    }

    private function createSessionSecurity(SessionValidator $sessionValidator): SessionRequestSecurity
    {
        return new SessionRequestSecurity($this->createBasicSecurity(), $sessionValidator, $this->app['session'], $this->logger);
    }

    private function createCollection(string $environment, RequestSecurity $requestSecurity): Collection
    {
        return Collection::createRoot($this->app, $requestSecurity, $environment);
    }
}
