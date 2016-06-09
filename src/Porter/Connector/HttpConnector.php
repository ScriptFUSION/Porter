<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Options\HttpOptions;

class HttpConnector extends CachingConnector
{
    /** @var HttpOptions */
    private $options;

    public function __construct(HttpOptions $options = null)
    {
        parent::__construct();

        $this->options = $options ?: new HttpOptions;
    }

    public function fetchFreshData($source, EncapsulatedOptions $options = null)
    {
        return file_get_contents(
            $source,
            false,
            stream_context_create([
                'http' => array_intersect_key($options->copy(), $this->options->extractHttpContextOptions()),
            ])
        );
    }

    /**
     * @return HttpOptions
     */
    public function getOptions()
    {
        return $this->options;
    }
}
