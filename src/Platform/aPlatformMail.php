<?php
namespace Poirot\Mail\Platform;

use Poirot\ApiClient\aPlatform;


class aPlatformMail
    extends aPlatform
{
    protected $serverAddress;


    // options:

    /**
     * @return mixed
     */
    function getServerAddress()
    {
        return $this->serverAddress;
    }

    /**
     * Set Server Address
     *
     * $serverAddress:
     * - tcp://mail.example.com:25,
     * - ssh://hostname.com:2222
     *
     * @param string $serverAddress
     *
     * @return $this
     */
    function setServerAddress($serverAddress)
    {
        $this->serverAddress = (string) $serverAddress;
        return $this;
    }
}

