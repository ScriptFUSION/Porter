<?php
namespace ScriptFUSION\Porter\Provider;

class NullFetcher extends StaticDataFetcher
{
    public function __construct()
    {
        parent::__construct(new \EmptyIterator);
    }
}
