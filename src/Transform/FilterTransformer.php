<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Transform;

use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;

/**
 * Filters a collection of records based on the specified predicate function.
 *
 * This simple transformer is bundled with Porter as an example reference implementation for other transformers.
 */
class FilterTransformer implements Transformer
{
    public function __construct(private readonly \Closure $filter)
    {
    }

    public function transform(RecordCollection $records, $context): RecordCollection
    {
        $filter = static function ($predicate) use ($records, $context): \Generator {
            foreach ($records as $record) {
                if ($predicate($record, $context)) {
                    yield $record;
                }
            }
        };

        return new FilteredRecords($filter($this->filter), $records, $filter);
    }
}
