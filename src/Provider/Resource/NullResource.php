<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider\Resource;

final class NullResource extends StaticResource
{
    public function __construct()
    {
        parent::__construct(new \EmptyIterator);
    }
}
