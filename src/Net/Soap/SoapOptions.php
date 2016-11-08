<?php
namespace ScriptFUSION\Porter\Net\Soap;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Encapsulates SOAP context options
 */
final class SoapOptions extends EncapsulatedOptions
{
    public function __construct()
    {
        $this->setDefaults([
            'params' => [],
        ]);
    }

    public function getParameters()
    {
        return $this->get('params');
    }

    public function setParameters(array $params)
    {
        return $this->set('params', $params);
    }

    public function getVersion()
    {
        return $this->get('soap_version');
    }

    public function setVersion($version)
    {
        return $this->set('soap_version', $version|0);
    }

    public function getCompression()
    {
        return $this->get('compression');
    }

    public function setCompression($compression)
    {
        return $this->set('compression', $compression|0);
    }

    public function getKeepAlive()
    {
        return $this->get('keep_alive');
    }

    public function setKeepAlive($keepAlive)
    {
        return $this->set('keep_alive', (bool)$keepAlive);
    }

    public function setProxyHost($host)
    {
        return $this->set('proxy_host', "$host");
    }

    public function getProxyHost()
    {
        return $this->get('proxy_host');
    }

    public function setProxyPort($port)
    {
        return $this->set('proxy_port', $port|0);
    }

    public function getProxyPort()
    {
        return $this->get('proxy_port');
    }

    public function setProxyLogin($login)
    {
        return $this->set('proxy_login', "$login");
    }

    public function getProxyLogin()
    {
        return $this->get('proxy_login');
    }

    public function setProxyPassword($password)
    {
        return $this->set('proxy_password', "$password");
    }

    public function getProxyPassword()
    {
        return $this->get('proxy_password');
    }

    public function setEncoding($encoding)
    {
        return $this->set('encoding', "$encoding");
    }

    public function getEncoding()
    {
        return $this->get('encoding');
    }

    public function setTrace($trace)
    {
        return $this->set('trace', (bool)$trace);
    }

    public function getTrace()
    {
        return $this->get('trace');
    }

    public function setExceptions($exceptions)
    {
        return $this->set('exceptions', (bool)$exceptions);
    }

    public function getExceptions()
    {
        return $this->get('exceptions');
    }

    public function setClassmap(array $classmap)
    {
        return $this->set('classmap', $classmap);
    }

    public function getClassmap()
    {
        return $this->get('classmap');
    }

    public function setConnectionTimeout($timeout)
    {
        return $this->set('connection_timeout', $timeout|0);
    }

    public function getConnectionTimeout()
    {
        return $this->get('connection_timeout');
    }

    public function setTypemap(array $typemap)
    {
        return $this->set('typemap', $typemap);
    }

    public function getTypemap()
    {
        return $this->get('typemap');
    }

    public function setCacheWsdl($cacheWsdl)
    {
        return $this->set('cache_wsdl', $cacheWsdl|0);
    }

    public function getCacheWsdl()
    {
        return $this->get('cache_wsdl');
    }

    public function setUserAgent($userAgent)
    {
        return $this->set('user_agent', "$userAgent");
    }

    public function getUserAgent()
    {
        return $this->get('user_agent');
    }

    public function setFeatures($features)
    {
        return $this->set('features', $features|0);
    }

    public function getFeatures()
    {
        return $this->get('features');
    }

    public function setSslMethod($method)
    {
        return $this->set('ssl_method', $method);
    }

    public function getSslMethod()
    {
        return $this->get('ssl_method');
    }

    /**
     * Extracts a list of SOAP Client options only.
     *
     * @return array SOAP context options.
     *
     * @see http://php.net/manual/en/soapclient.soapclient.php
     */
    public function extractSoapClientOptions()
    {
        return array_intersect_key(
            $this->copy(),
            array_flip([
                'location',
                'uri',
                'style',
                'use',
                'soap_version',
                'proxy_host',
                'proxy_port',
                'proxy_login',
                'proxy_password',
                'local_cert',
                'passphrase',
                'authentication',
                'compression',
                'encoding',
                'trace',
                'classmap',
                'exceptions',
                'connection_timeout',
                'typemap',
                'cache_wsdl',
                'user_agent',
                'stream_context',
                'features',
                'keep_alive',
                'ssl_method',
            ])
        );
    }
}
