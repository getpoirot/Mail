<?php
namespace Poirot\Mail\Header;

use Poirot\Ioc\Container\aContainerCapped;
use Poirot\Ioc\Container\Exception\exContainerInvalidServiceType;
use Poirot\Mail\Interfaces\iHeader;


class PluginsHeader
    extends aContainerCapped
{
    /**
     * Validate Plugin Instance Object
     *
     * @param mixed $pluginInstance
     *
     * @throws exContainerInvalidServiceType
     * @return void
     */
    function validateService($pluginInstance)
    {
        if (! $pluginInstance instanceof iHeader )
            throw new exContainerInvalidServiceType(
                'Invalid Plugin Of Header Instance Provided.'
            );
    }
}
