<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;
use ScriptFUSION\Retry\FailingTooHardException;
use ScriptFUSIONTest\FixtureFactory;
use ScriptFUSIONTest\MockFactory;
use ScriptFUSIONTest\Stubs\TestRecoverableException;
use ScriptFUSIONTest\Stubs\TestRecoverableExceptionHandler;

/**
 * @see ImportConnector
 */
final class ImportConnectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    /**
     * Tests that when retry() is called multiple times, the original fetch exception handler is unmodified.
     * This is expected because the handler must be cloned using the prototype pattern to ensure multiple concurrent
     * fetches do not conflict.
     *
     * @dataProvider provideHandlerAndContext
     */
    public function testFetchExceptionHandlerCloned(
        TestRecoverableExceptionHandler $handler,
        ImportConnector $connector
    ): void {
        $handler->initialize();
        $initial = $handler->getCurrent();

        $connector->fetch('foo');

        self::assertSame($initial, $handler->getCurrent());
    }

    public function provideHandlerAndContext(): \Generator
    {
        yield 'User exception handler' => [
            $handler = new TestRecoverableExceptionHandler,
            $connector = FixtureFactory::buildImportConnector(
                \Mockery::mock(Connector::class)
                    ->shouldReceive('fetch')
                    ->andReturnUsing($this->createExceptionThrowingClosure())
                    ->getMock(),
                null,
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
            null,
            $handler = new StatelessRecoverableExceptionHandler(static function (): void {
                // Intentionally empty.
            })
        );

        $connector->fetch('foo');

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
            null,
            new StatelessRecoverableExceptionHandler(static function () {
                return false;
            })
        )->fetch('foo');
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
            null,
            self::createAsyncRecoverableExceptionHandler()
        );

        try {
            \Amp\Promise\wait($connector->fetchAsync('foo'));
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
            \Amp\Promise\wait($connector->fetchAsync('foo'));
        } catch (FailingTooHardException $exception) {
            // This is fine.
        }

        self::assertTrue(isset($exception));
    }

    /**
     * Tests that when user and resource recoverable exception handlers both return promises, both promises are
     * resolved.
     */
    public function testAsyncUserAndResourceRecoverablExceptionHandlers(): void
    {
        $connector = FixtureFactory::buildImportConnector(
            \Mockery::mock(Connector::class)
                ->shouldReceive('fetchAsync')
                    ->andThrow(new TestRecoverableException)
                ->getMock(),
            null,
            self::createAsyncRecoverableExceptionHandler()
        );

        $connector->setRecoverableExceptionHandler(self::createAsyncRecoverableExceptionHandler());

        try {
            \Amp\Promise\wait($connector->fetchAsync('foo'));
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
