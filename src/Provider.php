<?php

namespace Emartech\Silex\SecureController;

interface Provider
{
    public function setupActions(Collection $controllers);
}
