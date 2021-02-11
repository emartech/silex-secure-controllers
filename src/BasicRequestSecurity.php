<?php

namespace Emartech\Silex\SecureController;

use Psr\Log\LoggerInterface;
use Escher\Provider as EscherProvider;
use Escher\Exception as EscherException;

use SessionValidator\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Emartech\Jwt\Jwt;

class BasicRequestSecurity implements RequestSecurity
{
    private $escherProvider;
    private $logger;
    private $client;


    public function __construct(LoggerInterface $logger, EscherProvider $escherProvider, Client $client)
    {
        $this->logger = $logger;
        $this->escherProvider = $escherProvider;
        $this->client = $client;
    }

    public function validateSession(Request $request)
    {
        return null;
    }

    public function escherAuthenticate(Request $request)
    {
        try {
            $this->escherProvider->createEscher()->authenticate(
                $this->escherProvider->getKeyDB(),
                $request->server->all()
            );

            return null;
        } catch (EscherException $ex) {
            $this->logger->error('escher_auth_failure', ['exception' => $ex]);
            return new Response($ex->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    public function forceHttps(Request $request)
    {
        if ($this->forwardedProtocolIsHttps($request)) {
            return null;
        }

        $errorMessage = 'Service only works over HTTPS protocol.';
        $this->logger->info($errorMessage);
        return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
    }

    private function forwardedProtocolIsHttps(Request $request): bool
    {
        return 'https' == $request->headers->get('X-Forwarded-Proto', 'http');
    }

    public function getScheme(Request $request): string
    {
        $scheme = $request->getScheme();
        if ($this->forwardedProtocolIsHttps($request)) {
            $scheme = 'https';
            return $scheme;
        }
        return $scheme;
    }

    public function jwtAuthenticate(Request $request)
    {
        $authHeader = $request->headers->get("Authorization");

        if (!$authHeader) {
            $this->logger->error('Authorization header missing');
            return new Response('Token validation failed', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->validateJWT($authHeader);

            return null;
        } catch (\Exception $ex) {
            $errorMessage = 'JWT token validation failed: ' . $ex->getMessage();
            $this->logger->error($errorMessage);
            $this->logger->debug($errorMessage, [ 'exception' => $ex ]);
            return new Response('Token validation failed', Response::HTTP_UNAUTHORIZED);
        }
    }

    private function validateJWT(string $authHeader): void
    {
        $jwt = Jwt::create()->parseHeader($authHeader);

        $this->logger->debug("Validating session", ['msid' => $jwt->msid]);

        if (!isset($jwt->msid)) {
            throw new \Exception('MSID is missing');
        }

        if (!$this->client->isValid($jwt->msid)) {
            throw new \Exception('MSID is not valid');
        }
    }
}
