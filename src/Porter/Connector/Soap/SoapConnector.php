<?php
namespace ScriptFUSION\Porter\Connector\Soap;

use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Type\ObjectType;

class SoapConnector extends CachingConnector
{
    private $client;

    private $options;

    public function __construct($wsdl = null, SoapOptions $options = null)
    {
        parent::__construct();

        $this->client = new \SoapClient($wsdl, $options->extractSoapClientOptions());
        $this->options = $options;
    }

    public function fetchFreshData($source, EncapsulatedOptions $options = null)
    {
        if (!$options instanceof SoapOptions) {
            throw new \RuntimeException('Options must be an instance of SoapOptions.');
        }

        $params = array_merge($this->options->getParameters(), $options->getParameters());

        return ObjectType::toArray(
            \igorw\retry(5, function () use ($source, $params) {
                return $this->client->$source($params);
            })
        );
    }
}
