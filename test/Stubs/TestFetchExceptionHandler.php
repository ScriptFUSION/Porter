<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Stubs;

use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;

final class TestFetchExceptionHandler implements FetchExceptionHandler
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

    public function __invoke(\Exception $exception): void
    {
        $this->series->next();
    }

    public function getCurrent()
    {
        return $this->series->current();
    }
}
