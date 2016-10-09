<?php
namespace ScriptFUSION\Porter;

final class ProviderNotFoundException extends \RuntimeException
{
    public function __construct($message, \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
