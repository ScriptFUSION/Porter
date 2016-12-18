<?php
namespace ScriptFUSION\Porter\Transform;

use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;

class FilterTransformer implements Transformer
{
    /**
     * @var callable
     */
    private $filter;

    /**
     * @param callable $filter
     */
    public function __construct(callable $filter)
    {
        $this->filter = $filter;
    }

    public function transform(RecordCollection $records, $context)
    {
        $filter = function ($predicate) use ($records, $context) {
            foreach ($records as $record) {
                if ($predicate($record, $context)) {
                    yield $record;
                }
            }
        };

        return new FilteredRecords($filter($this->filter), $records, $filter);
    }
}
