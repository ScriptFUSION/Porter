<?php
namespace ScriptFUSION\Porter\Provider\Resource;

class NullResource extends StaticResource
{
    public function __construct()
    {
        parent::__construct(new \EmptyIterator);
    }
}
