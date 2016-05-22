<?php
namespace ScriptFUSION\Porter\Connector;

use ScriptFUSION\Porter\Options\HttpOptions;

class HttpConnector implements Connector
{
    /** @var HttpOptions */
    private $options;

    public function __construct(HttpOptions $options = null)
    {
        $this->options = $options ?: new HttpOptions;
    }

    public function fetch($source, array $parameters = [])
    {
        return file_get_contents(
            $source,
            false,
            stream_context_create(['http' => $this->options->extractHttpContextOptions()])
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
