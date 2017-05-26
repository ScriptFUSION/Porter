<?php
namespace ScriptFUSION\Porter\Net\Ssl;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Encapsulates SSL context options.
 *
 * @see http://php.net/manual/en/context.ssl.php
 */
final class SslOptions extends EncapsulatedOptions
{
    /**
     * @return string
     */
    public function getPeerName()
    {
        return $this->get('peer_name');
    }

    /**
     * @param string $peerName
     *
     * @return $this
     */
    public function setPeerName($peerName)
    {
        return $this->set('peer_name', "$peerName");
    }

    /**
     * @return bool
     */
    public function getVerifyPeer()
    {
        return $this->get('verify_peer');
    }

    /**
     * @param bool $verifyPeer
     *
     * @return $this
     */
    public function setVerifyPeer($verifyPeer)
    {
        return $this->set('verify_peer', (bool)$verifyPeer);
    }

    /**
     * @return bool
     */
    public function getVerifyPeerName()
    {
        return $this->get('verify_peer_name');
    }

    /**
     * @param bool $verifyPeerName
     *
     * @return $this
     */
    public function setVerifyPeerName($verifyPeerName)
    {
        return $this->set('verify_peer_name', (bool)$verifyPeerName);
    }

    /**
     * @return bool
     */
    public function getAllowSelfSigned()
    {
        return $this->get('allow_self_signed');
    }

    /**
     * @param bool $allowSelfSigned
     *
     * @return $this
     */
    public function setAllowSelfSigned($allowSelfSigned)
    {
        return $this->set('allow_self_signed', (bool)$allowSelfSigned);
    }

    /**
     * @return string
     */
    public function getCertificateAuthorityFilePath()
    {
        return $this->get('cafile');
    }

    /**
     * @param string $certificateAuthorityFilePath
     *
     * @return $this
     */
    public function setCertificateAuthorityFilePath($certificateAuthorityFilePath)
    {
        return $this->set('cafile', "$certificateAuthorityFilePath");
    }

    /**
     * @return string
     */
    public function getCertificateAuthorityDirectory()
    {
        return $this->get('capath');
    }

    /**
     * @param string $certificateAuthorityDirectory
     *
     * @return $this
     */
    public function setCertificateAuthorityDirectory($certificateAuthorityDirectory)
    {
        return $this->set('capath', "$certificateAuthorityDirectory");
    }

    /**
     * @return string
     */
    public function getCertificateFilePath()
    {
        return $this->get('local_cert');
    }

    /**
     * @param string $certificateFilePath
     *
     * @return $this
     */
    public function setCertificateFilePath($certificateFilePath)
    {
        return $this->set('local_cert', "$certificateFilePath");
    }

    /**
     * @return string
     */
    public function getCertificatePassphrase()
    {
        return $this->get('passphrase');
    }

    /**
     * @param string $certificatePassphrase
     *
     * @return $this
     */
    public function setCertificatePassphrase($certificatePassphrase)
    {
        return $this->set('passphrase', "$certificatePassphrase");
    }

    /**
     * @return string
     */
    public function getPrivateKeyFilePath()
    {
        return $this->get('local_pk');
    }

    /**
     * @param string $privateKeyFilePath
     *
     * @return $this
     */
    public function setPrivateKeyFilePath($privateKeyFilePath)
    {
        return $this->set('local_pk', "$privateKeyFilePath");
    }

    /**
     * @return int
     */
    public function getVerificationDepth()
    {
        return $this->get('verify_depth');
    }

    /**
     * @param int $verificationDepth
     *
     * @return $this
     */
    public function setVerificationDepth($verificationDepth)
    {
        return $this->set('verify_depth', $verificationDepth | 0);
    }

    /**
     * @return string
     */
    public function getCiphers()
    {
        return $this->get('ciphers');
    }

    /**
     * @param string $ciphers
     *
     * @return $this
     */
    public function setCiphers($ciphers)
    {
        return $this->set('ciphers', "$ciphers");
    }

    /**
     * @return bool
     */
    public function getCapturePeerCertificate()
    {
        return $this->get('capture_peer_cert');
    }

    /**
     * @param bool $capturePeerCertificate
     *
     * @return $this
     */
    public function setCapturePeerCertificate($capturePeerCertificate)
    {
        return $this->set('capture_peer_cert', (bool)$capturePeerCertificate);
    }

    /**
     * @return bool
     */
    public function getCapturePeerCertificateChain()
    {
        return $this->get('capture_peer_cert_chain');
    }

    /**
     * @param bool $capturePeerCertificateChain
     *
     * @return $this
     */
    public function setCapturePeerCertificateChain($capturePeerCertificateChain)
    {
        return $this->set('capture_peer_cert_chain', (bool)$capturePeerCertificateChain);
    }

    /**
     * @return bool
     */
    public function getSniEnabled()
    {
        return $this->get('SNI_enabled');
    }

    /**
     * @param bool $sniEnabled
     *
     * @return $this
     */
    public function setSniEnabled($sniEnabled)
    {
        return $this->set('SNI_enabled', (bool)$sniEnabled);
    }

    /**
     * @return bool
     */
    public function getDisableCompression()
    {
        return $this->get('disable_compression');
    }

    /**
     * @param bool $disableCompression
     *
     * @return $this
     */
    public function setDisableCompression($disableCompression)
    {
        return $this->set('disable_compression', (bool)$disableCompression);
    }

    /**
     * @return string|array
     */
    public function getPeerFingerprint()
    {
        return $this->get('peer_fingerprint');
    }

    /**
     * @param string|array $peerFingerprint
     *
     * @return $this
     */
    public function setPeerFingerprint($peerFingerprint)
    {
        return $this->set('peer_fingerprint', is_array($peerFingerprint) ? $peerFingerprint : "$peerFingerprint");
    }

    /**
     * Extracts a list of SSL context options.
     *
     * @return array SSL context options.
     */
    public function extractSslContextOptions()
    {
        return $this->copy();
    }
}
