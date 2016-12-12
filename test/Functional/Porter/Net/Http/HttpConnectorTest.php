<?php
namespace ScriptFUSIONTest\Functional\Porter\Net\Http;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Net\Http\HttpConnectionException;
use ScriptFUSION\Porter\Net\Http\HttpConnector;
use ScriptFUSION\Porter\Net\Http\HttpOptions;
use ScriptFUSION\Porter\Net\Http\HttpServerException;
use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;
use Symfony\Component\Process\Process;

final class HttpConnectorTest extends \PHPUnit_Framework_TestCase
{
    const HOST = '[::1]:12345';
    const URI = '/test?baz=qux';

    private static $dir;

    /** @var HttpConnector */
    private $connector;

    public static function setUpBeforeClass()
    {
        self::$dir = __DIR__ . '/servers';
    }

    protected function setUp()
    {
        $this->connector = new HttpConnector;
    }

    public function testConnectionToLocalWebserver()
    {
        $server = $this->startServer('feedback');
        $response = $this->fetch(new HttpConnector((new HttpOptions)->addHeader($header = 'Foo: Bar')));
        $this->stopServer($server);

        self::assertRegExp('[\AGET \Q' . self::HOST . self::URI . '\E HTTP/\d+\.\d+$]m', $response);
        self::assertRegExp("[^$header$]m", $response);
    }

    public function testConnectionTimeout()
    {
        $this->setExpectedException(HttpConnectionException::class);

        $this->fetch();
    }

    public function testErrorResponse()
    {
        $server = $this->startServer('404');

        $this->setExpectedExceptionRegExp(HttpServerException::class, '[^foo\z]m');

        try {
            $this->fetch();
        } finally {
            $this->stopServer($server);
        }
    }

    /**
     * @param string $script
     *
     * @return Process Server.
     */
    private function startServer($script)
    {
        $server = (
            new Process(sprintf(
                '%sphp -S %s %s.php',
                // Prevent forking on some Unix systems.
                file_exists('/bin/sh') ? 'exec ' : '',
                self::HOST,
                $script
            ))
        )->setWorkingDirectory(self::$dir);
        $server->start();

        // Wait for server to spawn.
        \ScriptFUSION\Retry\retry(5, function () {
            $this->fetch();
        }, function (\Exception $exception) {
            static $handler;
            $handler = $handler ?: new ExponentialBackoffExceptionHandler();

            if (!$exception instanceof HttpConnectionException) {
                return false;
            }

            return $handler();
        });

        return $server;
    }

    private function stopServer(Process $server)
    {
        $server->stop();
    }

    private function fetch(Connector $connector = null)
    {
        $connector = $connector ?: $this->connector;

        return $connector->fetch('http://' . self::HOST . self::URI);
    }
}
