<?php

namespace Emartech\Silex\SecureController;

use Symfony\Component\HttpFoundation\Request;


interface RequestSecurity
{
    public function validateSession();

    public function escherAuthenticate();

    public function forceHttps(Request $request);

    public function getScheme(Request $request): string;

    public function jwtAuthenticate(Request $request);
}
