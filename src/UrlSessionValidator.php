<?php

namespace Emartech\Silex\SecureController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class UrlSessionValidator implements SessionValidator
{
    /**
     * @var string
     */
    private $sessionKey;

    /**
     * @var string
     */
    private $urlKey;

    public function __construct(string $sessionKey, string $urlKey)
    {
        $this->sessionKey = $sessionKey;
        $this->urlKey = $urlKey;
    }

    public function isValid(Session $session, Request $request)
    {
        return $session->has($this->sessionKey)
            && $session->get($this->sessionKey) === $request->attributes->getAlnum($this->urlKey);
    }
}
