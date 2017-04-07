<?php

namespace Emartech\Silex\SecureController;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionRequestSecurity implements RequestSecurity
{
    /**
     * @var RequestSecurity
     */
    private $delegate;

    /**
     * @var SessionValidator
     */
    private $sessionValidator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(RequestSecurity $delegate, SessionValidator $sessionValidator, Session $session, LoggerInterface $logger)
    {
        $this->delegate = $delegate;
        $this->sessionValidator = $sessionValidator;
        $this->session = $session;
        $this->logger = $logger;
    }

    public function validateSession(Request $request)
    {
        if (!$this->sessionValidator->isValid($this->session, $request)) {
            $this->logger->error(json_encode([
                'error_message' => "Invalid session",
                'error_class' => "Invalid session",
                'error_name' => 'session_validation_failure',
                'error_code' => 1,
            ]));

            return new Response("Invalid session", Response::HTTP_UNAUTHORIZED);
        }
        return null;
    }

    public function escherAuthenticate()
    {
        return $this->delegate->escherAuthenticate();
    }

    public function forceHttps(Request $request)
    {
        return $this->delegate->forceHttps($request);
    }

    public function getScheme(Request $request): string
    {
        return $this->delegate->getScheme($request);
    }

    public function jwtAuthenticate(Request $request)
    {
        return $this->jwtAuthenticate($request);
    }
}
