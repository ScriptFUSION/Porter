<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Connector\Recoverable;

use Amp\PHPUnit\AsyncTestCase;
use ScriptFUSION\Porter\Connector\Recoverable\ExponentialAsyncDelayRecoverableExceptionHandler;
use ScriptFUSIONTest\Stubs\TestRecoverableException;

/**
 * @see ExponentialAsyncDelayRecoverableExceptionHandler
 */
final class ExponentialAsyncDelayRecoverableExceptionHandlerTest extends AsyncTestCase
{
    /** @var ExponentialAsyncDelayRecoverableExceptionHandler */
    private $handler;

    /** @var TestRecoverableException */
    private $exception;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ExponentialAsyncDelayRecoverableExceptionHandler;
        $this->handler->initialize();
        $this->exception = new TestRecoverableException;
    }

    /**
     * Tests that when the handler is invoked, each delay is longer than the last.
     */
    public function testDelay(): \Generator
    {
        for ($i = $previousDuration = 0; $i < 4; ++$i) {
            $start = microtime(true);
            yield ($this->handler)($this->exception);
            $duration = microtime(true) - $start;

            self::assertGreaterThan($previousDuration, $duration);
            $previousDuration = $duration;
        }

        self::assertGreaterThan(.8, $previousDuration);
    }
}
