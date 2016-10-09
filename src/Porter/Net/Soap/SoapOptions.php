<?php
namespace ScriptFUSION\Porter\Net\Soap;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * TODO. Add missing SOAP context option accessors and mutators.
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
