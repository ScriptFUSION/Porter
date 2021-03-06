<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Provider\Resource\StaticResource;

class StaticDataImportSpecification extends ImportSpecification
{
    public function __construct(\Iterator $data)
    {
        parent::__construct(new StaticResource($data));
    }
}
