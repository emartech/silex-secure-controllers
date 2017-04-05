<?php

namespace Emartech\Silex\SecureController;

use Symfony\Component\HttpFoundation\Session\Session;

class KeyExistenceSessionValidator implements SessionValidator
{
    /**
     * @var string
     */
    private $sessionKey;

    public function __construct(string $sessionKey)
    {
        $this->sessionKey = $sessionKey;
    }

    public function isValid(Session $session)
    {
        return $session->has($this->sessionKey);
    }
}
