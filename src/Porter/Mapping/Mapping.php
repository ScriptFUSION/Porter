<?php
namespace ScriptFUSION\Porter\Mapping;

abstract class Mapping extends \ArrayObject
{
    public function __construct()
    {
        parent::__construct($this->createMap());
    }

    abstract public function createMap();
}
