<?php

use Emartech\Silex\SecureController\RequestSecurity;
use Emartech\TestHelper\BaseTestCase;
use Escher\Escher;
use Escher\Exception as EscherException;
use Escher\Provider as EscherProvider;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestSecurityTest extends BaseTestCase
{
    /** @var EscherProvider|PHPUnit_Framework_MockObject_MockObject */
    private $escherProviderMock;
    /** @var LoggerInterface|PHPUnit_Framework_MockObject_MockObject */
    private $loggerMock;
    /** @var RequestSecurity */
    private $subject;


    public function setUp()
    {
        $this->escherProviderMock = $this->mock(EscherProvider::class);
        $this->loggerMock = $this->mock(LoggerInterface::class);
        $this->subject = new RequestSecurity($this->loggerMock, $this->escherProviderMock);
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

        $actual = $this->subject->escherAuthenticate();

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

        $this->assertNull($this->subject->escherAuthenticate());
    }

    /**
     * @test
     */
    public function forceHttps_ProdEnvWithHttp_BadRequest()
    {
        $request = $this->getRequestMockWithProtocol('http');
        $actual = $this->subject->forceHttps($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $actual->getStatusCode());
    }

    /**
     * @test
     */
    public function forceHttps_ProdEnvWithHttps_BadRequest()
    {
        $request = $this->getRequestMockWithProtocol('https');
        $this->assertNull($this->subject->forceHttps($request));
    }

    private function getRequestMockWithProtocol($protocol)
    {
        $request = $this->mock(Request::class);
        $request->expects($this->once())
            ->method('isSecure')
            ->will($this->returnValue($protocol === 'https'));

        return $request;
    }
}
