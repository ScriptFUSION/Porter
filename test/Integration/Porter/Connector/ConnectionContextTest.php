<?php
namespace ScriptFUSIONTest\Integration\Porter\Connector;

use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Connector\RecoverableConnectorException;
use ScriptFUSIONTest\FixtureFactory;
use ScriptFUSIONTest\Stubs\TestFetchExceptionHandler;

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

        $context->retry(static function () {
            static $invocationCount;

            if (!$invocationCount++) {
                throw new RecoverableConnectorException;
            }
        });

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
}
