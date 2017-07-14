<?php

namespace Emartech\Silex\SecureController;

use Symfony\Component\HttpFoundation\Request;


interface RequestSecurity
{
    public function validateSession(Request $request);

    public function escherAuthenticate(Request $request);

    public function forceHttps(Request $request);

    public function getScheme(Request $request): string;

    public function jwtAuthenticate(Request $request);
}
