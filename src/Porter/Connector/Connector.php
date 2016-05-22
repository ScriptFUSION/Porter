<?php
namespace ScriptFUSION\Porter\Connector;

interface Connector
{
    /**
     * @param string $source
     * @param array $parameters
     *
     * @return mixed
     */
    public function fetch($source, array $parameters = []);
}
