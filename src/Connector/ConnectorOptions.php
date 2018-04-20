<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

interface ConnectorOptions
{
    public function getOptions(): EncapsulatedOptions;
}
