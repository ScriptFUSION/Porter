<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Transform;

use Amp\Producer;
use ScriptFUSION\Porter\Collection\AsyncFilteredRecords;
use ScriptFUSION\Porter\Collection\AsyncRecordCollection;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;

/**
 * Filters a collection of records based on the specified predicate function.
 *
 * This simple transformer is bundled with Porter as an example reference implementation for other transformers.
 */
class FilterTransformer implements Transformer, AsyncTransformer
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

    public function transform(RecordCollection $records, $context): RecordCollection
    {
        $filter = static function ($predicate) use ($records, $context) {
            foreach ($records as $record) {
                if ($predicate($record, $context)) {
                    yield $record;
                }
            }
        };

        return new FilteredRecords($filter($this->filter), $records, $filter);
    }

    public function transformAsync(AsyncRecordCollection $records, $context): AsyncRecordCollection
    {
        return new AsyncFilteredRecords(
            new Producer(function (\Closure $emit) use ($records) {
                while (yield $records->advance()) {
                    if (($this->filter)($record = $records->getCurrent())) {
                        yield $emit($record);
                    }
                }
            }),
            $records,
            $this->filter
        );
    }
}
