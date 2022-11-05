<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Import;

use ScriptFUSION\Porter\Provider\Resource\StaticResource;

class StaticImport extends Import
{
    public function __construct(\Iterator $data)
    {
        parent::__construct(new StaticResource($data));
    }
}
