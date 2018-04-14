<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Transform;

use Amp\Promise;

interface AsyncTransformer
{
    public function transformAsync(array $record, $context): Promise;
}
