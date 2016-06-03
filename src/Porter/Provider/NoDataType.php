<?php
namespace ScriptFUSION\Porter\Provider;

class NoDataType extends StaticDataType
{
    public function __construct()
    {
        parent::__construct(new \EmptyIterator);
    }
}
