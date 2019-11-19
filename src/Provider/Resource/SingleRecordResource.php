<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider\Resource;

/**
 * Marker interface that specifies a resource that only fetches a single record and whose iterator is thus only valid
 * for one iteration. Such resources are intended to be used with Porter::importOne() rather than import().
 */
interface SingleRecordResource
{
    // Marker interface.
}
