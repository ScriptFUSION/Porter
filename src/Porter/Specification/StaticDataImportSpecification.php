<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Provider\StaticData;

class StaticDataImportSpecification extends ImportSpecification
{
    public function __construct(\Iterator $data)
    {
        parent::__construct(new StaticData($data));
    }
}
