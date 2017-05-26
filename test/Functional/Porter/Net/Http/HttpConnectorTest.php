<?php
namespace ScriptFUSIONTest\Functional\Porter\Net\Http;

use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Net\Http\HttpConnectionException;
use ScriptFUSION\Porter\Net\Http\HttpConnector;
use ScriptFUSION\Porter\Net\Http\HttpOptions;
use ScriptFUSION\Porter\Net\Http\HttpServerException;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;
use Symfony\Component\Process\Process;

final class HttpConnectorTest extends \PHPUnit_Framework_TestCase
{
    const HOST = '[::1]:12345';
    const SSL_HOST = '[::1]:6666';
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

    /**
     * @requires OS Linux
     */
    public function testSslConnectionToLocalWebserver()
    {
        $server = $this->startServer('feedback');
        $this->startSsl();

        $response = $this->fetchViaSsl(self::createUnverifiedSslConnector());

        self::stopSsl();
        $this->stopServer($server);

        self::assertRegExp('[\AGET \Q' . self::SSL_HOST . '\E/ HTTP/\d+\.\d+$]m', $response);
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
        } catch (HttpServerException $exception) {
            $this->assertSame('foo', $exception->getBody());

            throw $exception;
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
        $server = new Process(['php', '-S', self::HOST, "$script.php"], self::$dir);
        $server->start();

        self::waitForHttpServer(function () {
            $this->fetch();
        });

        return $server;
    }

    private function stopServer(Process $server)
    {
        $server->stop();
    }

    private function startSsl()
    {
        $accept = str_replace($filter = ['[', ']'], null, self::SSL_HOST);
        $connect = str_replace($filter, null, self::HOST);
        $certificate = tempnam(sys_get_temp_dir(), 'Porter');

        // Create SSL tunnel process.
        (new Process(
            // Generate self-signed SSL certificate in PEM format.
            "openssl req -new -x509 -nodes -batch -keyout '$certificate' -out '$certificate'

            { stunnel4 -fd 0 || stunnel -fd 0; } <<.
                # Disable PID to run as non-root user.
                pid=
                # Must run as foreground process on Travis, for some reason.
                foreground=yes

                []
                cert=$certificate
                accept=$accept
                connect=$connect
."
        ))->start();

        self::waitForHttpServer(function () {
            $this->fetchViaSsl(self::createUnverifiedSslConnector());
        });
    }

    private static function stopSsl()
    {
        `pkill stunnel`;
    }

    private function fetch(Connector $connector = null)
    {
        $connector = $connector ?: $this->connector;

        return $connector->fetch('http://' . self::HOST . self::URI);
    }

    private function fetchViaSsl(Connector $connector)
    {
        return $connector->fetch('https://' . self::SSL_HOST);
    }

    /**
     * Waits for the specified HTTP server invoker to stop throwing HttpConnectionExceptions.
     *
     * @param \Closure $serverInvoker HTTP server invoker.
     */
    private static function waitForHttpServer(\Closure $serverInvoker)
    {
        \ScriptFUSION\Retry\retry(
            ImportSpecification::DEFAULT_FETCH_ATTEMPTS,
            $serverInvoker,
            function (\Exception $exception) {
                if (!$exception instanceof HttpConnectionException) {
                    return false;
                }

                static $handler;
                $handler = $handler ?: new ExponentialBackoffExceptionHandler;

                return $handler();
            }
        );
    }

    /**
     * @return HttpConnector
     */
    private static function createUnverifiedSslConnector()
    {
        $connector = new HttpConnector($options = new HttpOptions);
        $options->getSslOptions()
            ->setVerifyPeer(false)
            ->setVerifyPeerName(false)
        ;

        return $connector;
    }
}
