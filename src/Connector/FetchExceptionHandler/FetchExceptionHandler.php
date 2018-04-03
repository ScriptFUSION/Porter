<?php
namespace ScriptFUSION\Porter\Connector\FetchExceptionHandler;

interface FetchExceptionHandler
{
    public function reset();

    public function __invoke(\Exception $exception);
}
