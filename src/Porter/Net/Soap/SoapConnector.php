<?php
namespace ScriptFUSION\Porter\Net\Soap;

use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Type\ObjectType;
use ScriptFUSION\Retry\ErrorHandler\ExponentialBackoffErrorHandler;

class SoapConnector extends CachingConnector
{
    private $client;

    private $options;

    public function __construct($wsdl = null, SoapOptions $options = null)
    {
        parent::__construct();

        $this->client = new \SoapClient($wsdl, $options ? $options->extractSoapClientOptions() : null);
        $this->options = $options;
    }

    public function fetchFreshData($source, EncapsulatedOptions $options = null)
    {
        if ($options && !$options instanceof SoapOptions) {
            throw new \InvalidArgumentException('Options must be an instance of SoapOptions.');
        }

        $params = array_merge($this->options->getParameters(), $options ? $options->getParameters() : []);

        return ObjectType::toArray(
            \ScriptFUSION\Retry\retry(5, function () use ($source, $params) {
                return $this->client->$source($params);
            }, new ExponentialBackoffErrorHandler)
        );
    }
}
