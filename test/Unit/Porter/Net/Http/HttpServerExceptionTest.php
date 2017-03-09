<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Http;

use ScriptFUSION\Porter\Connector\RecoverableConnectorException;
use ScriptFUSION\Porter\Net\Http\HttpServerException;

final class HttpServerExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpServerException
     */
    private $exception;

    protected function setUp()
    {
        $this->exception = new HttpServerException('foo', 123, 'bar');
    }

    public function testRecoverable()
    {
        self::assertInstanceOf(RecoverableConnectorException::class, $this->exception);
    }

    public function testMessage()
    {
        $this->assertSame('foo', $this->exception->getMessage());
    }

    public function testCode()
    {
        $this->assertSame(123, $this->exception->getCode());
    }

    public function testBody()
    {
        self::assertSame('bar', $this->exception->getBody());
    }
}
