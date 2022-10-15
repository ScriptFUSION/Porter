<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Transform;

use ScriptFUSION\Porter\Collection\AsyncRecordCollection;

/**
 * Provides a method to asynchronously transform imported data.
 */
interface AsyncTransformer extends AnysyncTransformer
{
    /**
     * Transforms the specified asynchronous record collection, decorated with the specified context data.
     *
     * @param AsyncRecordCollection $records Asynchronous Record collection.
     * @param mixed $context Context data.
     */
    public function transformAsync(AsyncRecordCollection $records, mixed $context): AsyncRecordCollection;
}
