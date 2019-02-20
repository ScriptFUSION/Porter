<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Stubs;

use Amp\Promise;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableException;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;

final class TestRecoverableExceptionHandler implements RecoverableExceptionHandler
{
    /**
     * @var \Generator
     */
    private $series;

    public function initialize(): void
    {
        $this->series = (static function (): \Generator {
            yield from range(1, 10);
        })();
    }

    public function __invoke(RecoverableException $exception): ?Promise
    {
        $this->series->next();

        return null;
    }

    public function getCurrent()
    {
        return $this->series->current();
    }
}
