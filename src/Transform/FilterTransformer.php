<?php
namespace ScriptFUSION\Porter\Transform;

use Amp\Promise;
use Amp\Success;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;

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

    public function transform(RecordCollection $records, $context)
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

    public function transformAsync(array $record, $context): Promise
    {
        return new Success(($this->filter)($record, $context) ? $record : null);
    }
}
