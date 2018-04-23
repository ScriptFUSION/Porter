<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Stubs;

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
            foreach (range(1, 10) as $value) {
                yield $value;
            }
        })();
    }

    public function __invoke(RecoverableException $exception): void
    {
        $this->series->next();
    }

    public function getCurrent()
    {
        return $this->series->current();
    }
}
