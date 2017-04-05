<?php

namespace Emartech\Silex\SecureController;

use Symfony\Component\HttpFoundation\Session\Session;

interface SessionValidator
{
    public function isValid(Session $session);
}
