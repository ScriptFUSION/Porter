<?php
namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Provider\DataSource\StaticDataSource;

class StaticDataImportSpecification extends ImportSpecification
{
    public function __construct(\Iterator $data)
    {
        parent::__construct(new StaticDataSource($data));
    }
}
