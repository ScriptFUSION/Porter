<?php
namespace ScriptFUSIONTest\Stubs;

use ScriptFUSION\Porter\Connector\FetchExceptionHandler\FetchExceptionHandler;

final class TestFetchExceptionHandler implements FetchExceptionHandler
{
    /**
     * @var \Generator
     */
    private $series;

    public function initialize()
    {
        $this->series = call_user_func(function () {
            foreach (range(1, 10) as $value) {
                yield $value;
            }
        });
    }

    public function __invoke(\Exception $exception)
    {
        $current = $this->getCurrent();

        $this->series->next();

        return $current;
    }

    public function getCurrent()
    {
        return $this->series->current();
    }
}
