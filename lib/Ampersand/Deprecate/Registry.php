<?php
/**
 * Ampersand IT Library
 *
 * @category    Ampersand_Library
 * @package     Ampersand_Deprecate
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Library
 * @package     Ampersand_Deprecate
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Deprecate_Registry extends Ampersand_Registry_Abstract
{
    const REGISTRY_NAMESPACE = 'Ampersand_Deprecate';
    
    /**
     * Resets the registry
     *
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function reset()
    {
        Ampersand_Registry::reset(self::REGISTRY_NAMESPACE);
    }
    
    /**
     * Gets a value from the container instance
     *
     * @param string $key
     * @return mixed
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function get($key)
    {
        return Ampersand_Registry::get($key, self::REGISTRY_NAMESPACE);
    }
    
    /**
     * Sets a value in the container instance
     *
     * @param string $key
     * @param mixed $value
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function set($key, $value)
    {
        Ampersand_Registry::set($key, $value, self::REGISTRY_NAMESPACE);
    }
    
    /**
     * Returns whether or not a key exists in the container instance
     *
     * @param string $key
     * @return bool
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function has($key)
    {
        return Ampersand_Registry::has($key, self::REGISTRY_NAMESPACE);
    }
}