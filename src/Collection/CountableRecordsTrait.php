<?php
namespace ScriptFUSION\Porter\Collection;

trait CountableRecordsTrait
{
    /** @var int */
    private $count;

    public function count()
    {
        return $this->count;
    }

    private function setCount($count)
    {
        $this->count = $count | 0;
    }
}
