<?php
namespace Poirot\Mail;

use Poirot\Mail\Header\GenericHeader;
use Poirot\Mail\Header\PluginsHeader;
use Poirot\Mail\Interfaces\iHeader;
use Poirot\Std\Exceptions\exImmutable;
use Poirot\Std\Interfaces\Pact\ipFactory;

/*

$h = \Poirot\Http\Header\FactoryHttpHeader::of([
    'Accept' => [
        ['audio/mp3', 'q'=>'0.2', 'version'=>'0.5']
        , 'audio/basic+mp3'
        // or
        , ['audio/basic+mp3']
    ]
]);

*/

class FactoryHeader
    implements ipFactory
{
    /** @var PluginsHeader */
    static protected $pluginManager;


    /**
     * Factory With Valuable Parameter
     *
     * @param array|string $valuable
     * array:
     * [ 'Accept' => [
     *     ['audio/mp3', 'q'=>'0.2', 'version'=>'0.5']
     *     , 'audio/basic+mp3'
     *   ]
     * ]
     *
     * Accept: audio/mp3; q=0.2; version=0.5, audio/basic+mp3
     *
     * @throws \Exception
     * @return iHeader
     */
    static function of($valuable)
    {
        // string:
        if ( \Poirot\Std\isStringify($valuable) ) {
            ## extract label and value from header
            $parsed = \Poirot\Mail\Header\splitLabelValue( (string) $valuable );
            if ($parsed === false)
                throw new \InvalidArgumentException(sprintf(
                    'Invalid Header (%s)'
                    , $valuable
                ));

            return self::of( array(key($parsed) => current($parsed)) );
        }

        // array:
        if (! is_array($valuable) )
            throw new \InvalidArgumentException(sprintf(
                'Header must be valid string or array [ $label => ["df"=>"val"] ] or ["label"=>$value]; given (%s).'
                , \Poirot\Std\flatten($valuable)
            ));

        # Construct Header
        #
        ## ['label' => $value| $values[] ]
        $label = key($valuable);
        $value = current($valuable);

        if (! is_array($value) )
            $value = [ $value ];

        if ( self::isEnabledPlugins() && self::plugins()->has($label) )
        {
            $header = self::plugins()->get($label, $value);
        }
        else
        {
            $header = new GenericHeader($label, $value);
        }

        return $header;
    }
    
    
    // ..

    /**
     * Is Enabled Plugins?
     *
     * - depends on IoC Container
     *
     * @return bool
     */
    static function isEnabledPlugins()
    {
        return class_exists('Poirot\Ioc\Container\aContainerCapped');
    }

    /**
     * Headers Plugin Manager
     *
     * @return PluginsHeader
     * @throws \Exception
     */
    static function plugins()
    {
        if (! self::isEnabledPlugins() )
            throw new \Exception('Using Plugins depends on Poirot/Ioc; that not exists currently.');


        if (! self::$pluginManager )
            self::$pluginManager = new PluginsHeader;

        return self::$pluginManager;
    }

    /**
     * Set Headers Plugin Manager
     *
     * @param PluginsHeader $pluginsManager
     */
    static function givePluginManager(PluginsHeader $pluginsManager)
    {
        if ( self::$pluginManager !== null )
            throw new exImmutable('Header Factory Has Plugin Manager, and can`t be changed.');

        self::$pluginManager = $pluginsManager;
    }
}
