<?php
namespace ScriptFUSIONTest;

use ScriptFUSION\Porter\Provider\ProviderDataFetcher;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\StaticClass;

final class MockFactory
{
    use StaticClass;

    public static function mockImportSpecification()
    {
        return \Mockery::mock(ImportSpecification::class, [\Mockery::mock(ProviderDataFetcher::class)]);
    }
}
