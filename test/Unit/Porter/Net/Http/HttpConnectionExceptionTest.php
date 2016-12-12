<?php
namespace ScriptFUSIONTest\Unit\Porter\Net\Http;

use ScriptFUSION\Porter\Connector\RecoverableConnectorException;
use ScriptFUSION\Porter\Net\Http\HttpConnectionException;

final class HttpConnectionExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testRecoverable()
    {
        self::assertInstanceOf(RecoverableConnectorException::class, new HttpConnectionException);
    }
}
