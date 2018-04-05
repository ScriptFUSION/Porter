<?php
namespace ScriptFUSIONTest\Integration\Porter\Connector;

use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Connector\FetchExceptionHandler\StatelessFetchExceptionHandler;
use ScriptFUSION\Porter\Connector\RecoverableConnectorException;
use ScriptFUSIONTest\FixtureFactory;
use ScriptFUSIONTest\Stubs\TestFetchExceptionHandler;

/**
 * @see ConnectionContext
 */
final class ConnectionContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that when retry() is called multiple times, the original fetch exception handler is unmodified.
     * This is expected because the handler must be cloned using the prototype pattern to ensure multiple concurrent
     * fetches do not conflict.
     *
     * @dataProvider provideHandlerAndContext
     */
    public function testFetchExceptionHandlerCloned(TestFetchExceptionHandler $handler, ConnectionContext $context)
    {
        $handler->initialize();
        $initial = $handler->getCurrent();

        $context->retry(self::createExceptionThrowingClosure());

        self::assertSame($initial, $handler->getCurrent());
    }

    public function provideHandlerAndContext()
    {
        yield 'User exception handler' => [
            $handler = new TestFetchExceptionHandler,
            FixtureFactory::buildConnectionContext(false, $handler),
        ];

        $context = FixtureFactory::buildConnectionContext();
        // It should be OK to reuse the handler here because the whole point of the test is that it's not modified.
        $context->setResourceFetchExceptionHandler($handler);
        yield 'Resource exception handler' => [$handler, $context];
    }

    /**
     * Tests that when retry() is called, a stateless fetch exception handler is neither cloned nor reinitialized.
     * For stateless handlers, initialization is a NOOP, so avoiding cloning is a small optimization.
     */
    public function testStatelessExceptionHandlerNotCloned()
    {
        $context = FixtureFactory::buildConnectionContext(
            false,
            $handler = new StatelessFetchExceptionHandler(static function () {
                // Intentionally empty.
            })
        );

        $context->retry(self::createExceptionThrowingClosure());

        self::assertSame(
            $handler,
            call_user_func(
                \Closure::bind(
                    function () {
                        return $this->fetchExceptionHandler;
                    },
                    $context,
                    $context
                )
            )
        );
    }

    /**
     * Creates a closure that only throws an exception on the first invocation.
     *
     * @return \Closure
     */
    private static function createExceptionThrowingClosure()
    {
        return static function () {
            static $invocationCount;

            if (!$invocationCount++) {
                throw new RecoverableConnectorException;
            }
        };
    }
}
