<?php
namespace Poirot\Mail\Interfaces;


interface iMailMessage
{
    function setHeaders();

    function headers();

    function setBody();

    function getBody();
}
