<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Async\Throttle\NullThrottle;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Specification\Specification;
use ScriptFUSION\StaticClass;

/**
 * @internal For internal use only.
 */
final class ImportConnectorFactory
{
    use StaticClass;

    public static function create(
        Provider $provider,
        Connector $connector,
        Specification $specification
    ): ImportConnector {
        $throttle = $specification->getThrottle();

        if ($throttle instanceof NullThrottle && $connector instanceof ThrottledConnector) {
            $throttle = $connector->getThrottle();
        }

        return new ImportConnector(
            $provider,
            $connector,
            $specification->getRecoverableExceptionHandler(),
            $specification->getMaxFetchAttempts(),
            $specification->mustCache(),
            $throttle ?? null
        );
    }
}
