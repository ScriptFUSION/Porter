<?php
namespace ScriptFUSION\Porter\Net\Soap;

use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Type\ObjectType;

/**
 * Fetches data from a SOAP service.
 */
class SoapConnector extends CachingConnector
{
    private $client;

    private $wsdl;

    private $options;

    public function __construct($wsdl = null, SoapOptions $options = null)
    {
        parent::__construct();

        $this->wsdl = $wsdl;
        $this->options = $options;
    }

    public function fetchFreshData($source, EncapsulatedOptions $options = null)
    {
        if ($options && !$options instanceof SoapOptions) {
            throw new \InvalidArgumentException('Options must be an instance of SoapOptions.');
        }

        $params = array_merge($this->options->getParameters(), $options ? $options->getParameters() : []);

        return ObjectType::toArray($this->getOrCreateClient()->$source($params));
    }

    private function getOrCreateClient()
    {
        return $this->client ?: $this->client =
            new \SoapClient($this->wsdl, $this->options ? $this->options->extractSoapClientOptions() : null);
    }
}
