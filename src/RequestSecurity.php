<?php

namespace Emartech\Silex\SecureController;

use Psr\Log\LoggerInterface;
use Escher\Provider as EscherProvider;
use Escher\Exception as EscherException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Emartech\Jwt\Jwt;


class RequestSecurity
{
    private $escherProvider;
    private $logger;


    public function __construct(LoggerInterface $logger, EscherProvider $escherProvider)
    {
        $this->logger = $logger;
        $this->escherProvider = $escherProvider;
    }

    public function escherAuthenticate()
    {
        try {
            $this->escherProvider->createEscher()->authenticate($this->escherProvider->getKeyDB());

            return null;
        } catch (EscherException $ex) {
            $this->logger->error(json_encode([
                'error_message' => $ex->getMessage(),
                'error_class' => get_class($ex),
                'error_name' => 'escher_auth_failure',
                'error_code' => $ex->getCode()
            ]));
            return new Response($ex->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }

    public function forceHttps(Request $request)
    {
        if ($request->isSecure()) {
            return null;
        }

        $errorMessage = 'Service only works over HTTPS protocol.';
        $this->logger->info(json_encode([
            'error_message' => $errorMessage,
            'error_name' => 'forwarded_protocol_not_https',
        ]));
        return new Response($errorMessage, Response::HTTP_BAD_REQUEST);
    }

    public function jwtAuthenticate(Request $request)
    {
        $authHeader = $request->headers->get("Authorization");

        if (!$authHeader) {
            $this->logger->error('Authorization header missing');
        }

        try {
            Jwt::create()->parseHeader($authHeader);

            return null;
        } catch (\Exception $ex) {
            $this->logger->error('JWT token validation failed: '. $ex->getMessage());
            $this->logger->debug((string)$ex);
            return new Response('Token validation failed', Response::HTTP_UNAUTHORIZED);
        }
    }
}
