<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Provider\StaticDataType;

class StaticDataImportSpecification extends ImportSpecification
{
    public function __construct(\Iterator $data)
    {
        parent::__construct(new StaticDataType($data));
    }
}
