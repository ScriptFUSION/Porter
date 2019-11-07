<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;
use ScriptFUSION\Retry\FailingTooHardException;
use ScriptFUSIONTest\FixtureFactory;
use ScriptFUSIONTest\MockFactory;
use ScriptFUSIONTest\Stubs\TestRecoverableException;
use ScriptFUSIONTest\Stubs\TestRecoverableExceptionHandler;
use function Amp\Promise\wait;

/**
 * @see ImportConnector
 */
final class ImportConnectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var DataSource|MockInterface */
    private $source;

    protected function setUp()
    {
        parent::setUp();

        $this->source = \Mockery::mock(DataSource::class);
    }

    /**
     * Tests that when retry() is called multiple times, the original fetch exception handler is unmodified.
     * This is expected because the handler must be cloned using the prototype pattern to ensure multiple concurrent
     * fetches do not conflict.
     *
     * @dataProvider provideHandlerAndConnector
     */
    public function testFetchExceptionHandlerCloned(
        TestRecoverableExceptionHandler $handler,
        ImportConnector $connector
    ): void {
        $handler->initialize();
        $initial = $handler->getCurrent();

        $connector->fetch($this->source);

        self::assertSame($initial, $handler->getCurrent());
    }

    public function provideHandlerAndConnector(): \Generator
    {
        yield 'User exception handler' => [
            $handler = new TestRecoverableExceptionHandler,
            $connector = FixtureFactory::buildImportConnector(
                \Mockery::mock(Connector::class)
                    ->shouldReceive('fetch')
                    ->andReturnUsing($this->createExceptionThrowingClosure())
                    ->getMock(),
                $handler
            ),
        ];

        // It should be OK to reuse the handler here because the whole point of the test is that it's not modified.
        $connector->setRecoverableExceptionHandler($handler);
        yield 'Resource exception handler' => [$handler, $connector];
    }

    /**
     * Tests that when retry() is called, a stateless fetch exception handler is neither cloned nor reinitialized.
     * For stateless handlers, initialization is a NOOP, so avoiding cloning is a small optimization.
     */
    public function testStatelessExceptionHandlerNotCloned(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                ->twice()
                ->andReturnUsing($this->createExceptionThrowingClosure())
                ->getMock(),
            $handler = new StatelessRecoverableExceptionHandler(static function (): void {
                // Intentionally empty.
            })
        );

        $connector->fetch($this->source);

        self::assertSame(
            $handler,
            \Closure::bind(
                function (): RecoverableExceptionHandler {
                    return $this->userExceptionHandler;
                },
                $connector,
                $connector
            )()
        );
    }

    /**
     * Tests that a recoverable exception handler cannot return false.
     */
    public function testExceptionHandlerCannotCancelRetries(): void
    {
        $this->expectException(\TypeError::class);

        FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetch')
                ->andThrow(new TestRecoverableException)
                ->getMock(),
            new StatelessRecoverableExceptionHandler(static function () {
                return false;
            })
        )->fetch($this->source);
    }

    /**
     * Tests that when a user recoverable exception handler returns a promise, the promise is resolved.
     */
    public function testAsyncUserRecoverableExceptionHandler(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetchAsync')
                ->andThrow(new TestRecoverableException)
                ->getMock(),
            self::createAsyncRecoverableExceptionHandler()
        );

        try {
            wait($connector->fetchAsync($this->source));
        } catch (FailingTooHardException $exception) {
            // This is fine.
        }

        self::assertTrue(isset($exception));
    }

    /**
     * Tests that when a resource recoverable exception handler returns a promise, the promise is resolved.
     */
    public function testAsyncResourceRecoverableExceptionHandler(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetchAsync')
                ->andThrow(new TestRecoverableException)
                ->getMock()
        );

        $connector->setRecoverableExceptionHandler(self::createAsyncRecoverableExceptionHandler());

        try {
            wait($connector->fetchAsync($this->source));
        } catch (FailingTooHardException $exception) {
            // This is fine.
        }

        self::assertTrue(isset($exception));
    }

    /**
     * Tests that when user and resource recoverable exception handlers both return promises, both promises are
     * resolved.
     */
    public function testAsyncUserAndResourceRecoverableExceptionHandlers(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetchAsync')
                ->andThrow(new TestRecoverableException)
                ->getMock(),
            self::createAsyncRecoverableExceptionHandler()
        );

        $connector->setRecoverableExceptionHandler(self::createAsyncRecoverableExceptionHandler());

        try {
            wait($connector->fetchAsync($this->source));
        } catch (FailingTooHardException $exception) {
            // This is fine.
        }

        self::assertTrue(isset($exception));
    }

    /**
     * Creates a closure that only throws an exception on the first invocation.
     */
    private static function createExceptionThrowingClosure(): \Closure
    {
        return static function (): void {
            static $invocationCount;

            if (!$invocationCount++) {
                throw new TestRecoverableException;
            }
        };
    }

    private static function createAsyncRecoverableExceptionHandler(): RecoverableExceptionHandler
    {
        return new StatelessRecoverableExceptionHandler(static function () {
            return MockFactory::mockPromise();
        });
    }
}
