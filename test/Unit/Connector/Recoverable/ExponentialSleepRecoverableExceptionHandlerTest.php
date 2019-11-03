<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Connector\Recoverable;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\Recoverable\ExponentialSleepRecoverableExceptionHandler;
use ScriptFUSIONTest\Stubs\TestRecoverableException;

/**
 * @see ExponentialSleepRecoverableExceptionHandler
 */
final class ExponentialSleepRecoverableExceptionHandlerTest extends TestCase
{
    /** @var ExponentialSleepRecoverableExceptionHandler */
    private $handler;

    /** @var TestRecoverableException */
    private $exception;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ExponentialSleepRecoverableExceptionHandler;
        $this->handler->initialize();
        $this->exception = new TestRecoverableException;
    }

    /**
     * Tests that when the handler is invoked, each delay is longer than the last.
     */
    public function testDelay(): void
    {
        for ($i = $previousDuration = 0; $i < 4; ++$i) {
            $start = microtime(true);
            ($this->handler)($this->exception);
            $duration = microtime(true) - $start;

            self::assertGreaterThan($previousDuration, $duration);
            $previousDuration = $duration;
        }

        self::assertGreaterThan(.8, $previousDuration);
    }
}
