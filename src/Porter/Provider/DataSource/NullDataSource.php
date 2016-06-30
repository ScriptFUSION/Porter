<?php
namespace ScriptFUSION\Porter\Provider\DataSource;

class NullDataSource extends StaticDataSource
{
    public function __construct()
    {
        parent::__construct(new \EmptyIterator);
    }
}
