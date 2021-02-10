<?php

use Emartech\Silex\SecureController\BasicRequestSecurity;
use Emartech\TestHelper\BaseTestCase;
use Escher\Escher;
use Escher\Exception as EscherException;
use Escher\Provider as EscherProvider;

use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;
use SessionValidator\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

class RequestSecurityTest extends BaseTestCase
{
    /** @var EscherProvider|PHPUnit_Framework_MockObject_MockObject */
    private $escherProviderMock;

    /** @var LoggerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;

    /** @var BasicRequestSecurity */
    private $requestSecurity;

    /** @var Request */
    private $request;

    /**
     * @var Client|PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionValidatorClient;


    public function setUp()
    {
        $this->escherProviderMock = $this->mock(EscherProvider::class);
        $this->loggerMock = $this->mock(LoggerInterface::class);
        $this->sessionValidatorClient = $this->mock(Client::class);
        $this->requestSecurity = new BasicRequestSecurity($this->loggerMock, $this->escherProviderMock, $this->sessionValidatorClient);
        $this->request = new Request();
    }

    /**
     * @test
     */
    public function escherAuthenticate_AuthFailure_Unauthorized()
    {
        $this->escherProviderMock
            ->expects($this->once())
            ->method('createEscher')
            ->will($this->throwException(new EscherException()));

        $actual = $this->requestSecurity->escherAuthenticate($this->request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $actual->getStatusCode());
    }

    /**
     * @test
     */
    public function escherAuthenticate_AuthSuccess_NullReturned()
    {
        $this->escherProviderMock
            ->expects($this->once())
            ->method('createEscher')
            ->will($this->returnValue($this->mock(Escher::class)));

        $this->assertNull($this->requestSecurity->escherAuthenticate($this->request));
    }

    /**
     * @test
     */
    public function forceHttps_ProdEnvWithHttp_BadRequest()
    {
        $request = $this->getRequestMockWithProtocol('http');
        $actual = $this->requestSecurity->forceHttps($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actual->getStatusCode());
    }

    /**
     * @test
     */
    public function forceHttps_ProdEnvWithHttps_BadRequest()
    {
        $request = $this->getRequestMockWithProtocol('https');
        $this->assertNull($this->requestSecurity->forceHttps($request));
    }

    /**
     * @test
     */
    public function jwtAuthenticate_authHeaderMissing_Unauthorized()
    {
        $request = $this->getRequestWithAuthorizationHeader('');
        $response = $this->requestSecurity->jwtAuthenticate($request);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function jwtAuthenticate_JWTAuthFails_Unauthorized()
    {
        $request = $this->getRequestWithAuthorizationHeader("invalid jwt");
        $response = $this->requestSecurity->jwtAuthenticate($request);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function jwtAuthenticate_MsidMissing_Unauthorized()
    {
        $secret = 'some_secret';
        putenv("JWT_SECRET=$secret");
        $jwt = JWT::encode(['customerId' => 1], $secret);
        $request = $this->getRequestWithAuthorizationHeader("Bearer $jwt");

        $response = $this->requestSecurity->jwtAuthenticate($request);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function jwtAuthenticate_MsidInvalid_Unauthorized()
    {
        $secret = 'some_secret';
        putenv("JWT_SECRET=$secret");
        $jwt = JWT::encode(['msid' => 'some id'], $secret);
        $request = $this->getRequestWithAuthorizationHeader("Bearer $jwt");

        $this->sessionValidatorClient->expects($this->once())->method('isValid')->willReturn(false);

        $response = $this->requestSecurity->jwtAuthenticate($request);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    private function getRequestWithAuthorizationHeader($authHeader): Request
    {
        $headers = $this->mock(ParameterBag::class);
        $headers->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->willReturn($authHeader);

        /** @var Request|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->mock(Request::class);
        $request->headers = $headers;

        return $request;
    }

    private function getRequestMockWithProtocol($protocol)
    {
        /** @var ParameterBag|PHPUnit_Framework_MockObject_MockObject $headers */
        $headers = $this->mock(ParameterBag::class);
        $headers->expects($this->once())
            ->method('get')
            ->with('X-Forwarded-Proto', 'http')
            ->will($this->returnValue($protocol));

        /** @var Request|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->mock(Request::class);
        $request->headers = $headers;
        return $request;
    }
}
