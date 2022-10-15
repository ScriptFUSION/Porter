<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\AsyncDataSource;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;
use ScriptFUSIONTest\FixtureFactory;
use ScriptFUSIONTest\Stubs\TestRecoverableException;
use ScriptFUSIONTest\Stubs\TestRecoverableExceptionHandler;

/**
 * @see ImportConnector
 */
final class ImportConnectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var DataSource|MockInterface */
    private $source;

    /** @var AsyncDataSource|MockInterface */
    private $asyncSource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->source = \Mockery::mock(DataSource::class);
        $this->asyncSource = \Mockery::mock(AsyncDataSource::class);
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
                    ->andReturnUsing(self::createExceptionThrowingClosure())
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
                ->andReturnUsing(self::createExceptionThrowingClosure())
                ->getMock(),
            $handler = new StatelessRecoverableExceptionHandler(static function (): void {
                // Intentionally empty.
            })
        );

        $connector->fetch($this->source);

        self::assertSame(
            $handler,
            \Closure::bind(
                fn () => $this->userExceptionHandler,
                $connector,
                $connector
            )()
        );
    }

    /**
     * Tests that when a user recoverable exception handler throws an exception, the handler's exception can be
     * captured.
     */
    public function testAsyncUserRecoverableExceptionHandler(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetchAsync')
                ->andThrow(new TestRecoverableException)
                ->getMock(),
            new StatelessRecoverableExceptionHandler(
                self::createExceptionThrowingClosure($exception = new TestRecoverableException)
            )
        );

        try {
            $connector->fetchAsync($this->asyncSource);
        } catch (\Exception $e) {
            self::assertSame($exception, $e);
        }
    }

    /**
     * Tests that when a resource recoverable exception handler throws an exception, the handler's exception can be
     * captured.
     */
    public function testAsyncResourceRecoverableExceptionHandler(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetchAsync')
                ->andThrow(new TestRecoverableException)
                ->getMock()
        );

        $connector->setRecoverableExceptionHandler(new StatelessRecoverableExceptionHandler(
            self::createExceptionThrowingClosure($exception = new TestRecoverableException)
        ));

        try {
            $connector->fetchAsync($this->asyncSource);
        } catch (\Exception $e) {
            self::assertSame($exception, $e);
        }
    }

    /**
     * Tests that when user and resource recoverable exception handlers are both set, both handlers are invoked,
     * resource handler first and user handler second.
     */
    public function testAsyncUserAndResourceRecoverableExceptionHandlers(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetchAsync')
                ->andThrow(new TestRecoverableException)
                ->getMock(),
            new StatelessRecoverableExceptionHandler(self::createExceptionThrowingClosure($e2 = new \Exception))
        );

        $connector->setRecoverableExceptionHandler(new StatelessRecoverableExceptionHandler(
            self::createExceptionThrowingClosure($e1 = new \Exception())
        ));

        try {
            $connector->fetchAsync($this->asyncSource);
        } catch (\Exception $exception) {
            self::assertSame($e1, $exception);
        }

        try {
            $connector->fetchAsync($this->asyncSource);
        } catch (\Exception $exception) {
            self::assertSame($e2, $exception);
        }
    }

    /**
     * Creates a closure that only throws an exception on the first invocation.
     */
    private static function createExceptionThrowingClosure(\Exception $exception = null): \Closure
    {
        return static function () use ($exception): void {
            static $invocationCount;

            if (!$invocationCount++) {
                throw $exception ?? new TestRecoverableException;
            }
        };
    }
}
