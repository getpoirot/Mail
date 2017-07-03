<?php
namespace Poirot\Mail;

use Poirot\Mail\Interfaces\iMailAddress;
use Poirot\Std\Struct\aValueObject;


class MailAddress
    extends aValueObject
    implements iMailAddress
{
    protected $email;
    protected $name;


    // Options:

    function setEmail($email)
    {
        $this->email = (string) $email;
        return $this;
    }

    /**
     * Retrieve email
     *
     * @return string
     */
    function getEmail()
    {
        return $this->email;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }
}