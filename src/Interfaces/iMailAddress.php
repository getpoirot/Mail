<?php
namespace Poirot\Mail\Interfaces;

use Poirot\Std\Interfaces\Struct\iValueObject;

/**
 * ! There is no validation implementation bound here in Value Object Itself
 *
 */
interface iMailAddress
    extends iValueObject
{
    /**
     * Retrieve email
     *
     * @return string
     */
    function getEmail();

    /**
     * Retrieve name
     *
     * @return string
     */
    function getName();
}
