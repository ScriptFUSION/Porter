<?php
namespace ScriptFUSIONTest\Functional\Porter\Net\Http;

use ScriptFUSION\Porter\Net\Http\HttpConnector;
use ScriptFUSION\Porter\Net\Http\HttpOptions;
use Symfony\Component\Process\Process;

final class HttpConnectorTest extends \PHPUnit_Framework_TestCase
{
    const HOST = 'localhost:12345';
    const URI = '/test?baz=qux';

    public function testConnectionToLocalWebserver()
    {
        $connector = new HttpConnector((new HttpOptions)->addHeader($header = 'Foo: Bar'));

        $process = (new Process(sprintf('php -S %s feedback.php', self::HOST)))->setWorkingDirectory(__DIR__);
        $process->start();
        $response = $connector->fetch('http://' . self::HOST . self::URI);
        $process->stop();

        self::assertRegExp('[\AGET \Q' . self::HOST . self::URI . '\E HTTP/\d+\.\d+$]m', $response);
        self::assertRegExp("[^$header$]m", $response);
    }
}
