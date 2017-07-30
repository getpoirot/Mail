<?php
namespace Poirot\Mail\Platform;

use Poirot\ApiClient\aPlatform;

/**
 * Use non-standard headers for broken MTAs.
 *
 * The default header EOL for headers is \r\n.  This causes problems
 * on some broken MTAs.  Setting this to TRUE will cause Elgg to use
 * \n, which will fix some problems sending email on broken MTAs.
 *
 *
 */
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

