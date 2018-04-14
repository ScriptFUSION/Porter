<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Specification;

use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Provider\Resource\PseudoBisyncResource;

class AsyncImportSpecification extends ImportSpecification
{
    private $asyncResource;

    public function __construct(AsyncResource $resource)
    {
        if (!$resource instanceof ProviderResource) {
            $resource = new PseudoBisyncResource($resource);
        }

        parent::__construct($resource);

        $this->asyncResource = $resource;
    }

    public function __clone()
    {
        parent::__clone();

        $this->asyncResource = clone $this->asyncResource;
    }

    final public function getAsyncResource(): AsyncResource
    {
        return $this->asyncResource;
    }
}
