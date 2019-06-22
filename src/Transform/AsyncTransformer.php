<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Transform;

use ScriptFUSION\Porter\Collection\AsyncRecordCollection;

interface AsyncTransformer extends AnysyncTransformer
{
    public function transformAsync(AsyncRecordCollection $records, $context): AsyncRecordCollection;
}
