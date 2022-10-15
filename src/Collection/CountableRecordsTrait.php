<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Collection;

trait CountableRecordsTrait
{
    private int $count;

    public function count(): int
    {
        return $this->count;
    }

    private function setCount(int $count): void
    {
        $this->count = $count;
    }
}
