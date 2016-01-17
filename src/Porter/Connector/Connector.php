<?php
namespace ScriptFUSION\Porter\Connector;

interface Connector
{
    public function fetch($destination, array $parameters = []);
}
