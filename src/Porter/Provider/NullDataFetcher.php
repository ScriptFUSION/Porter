<?php
namespace ScriptFUSION\Porter\Provider;

class NullDataFetcher extends StaticDataFetcher
{
    public function __construct()
    {
        parent::__construct(new \EmptyIterator);
    }
}
