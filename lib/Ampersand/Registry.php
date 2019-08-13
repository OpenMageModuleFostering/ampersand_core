<?php
/**
 * Ampersand IT Library
 *
 * @category    Ampersand_Library
 * @package     Ampersand_Registry
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Library
 * @package     Ampersand_Registry
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Registry
{
    /** @var Ampersand_Registry_Container */
    protected static $_globalContainer;
    /** @var array */
    protected static $_namespacedContainers = array();
    
    /**
     * Resets the specified registry. Resets all registries if no namespace is provided
     *
     * @param string $namespace OPTIONAL The namespace of the container to reset
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function reset($namespace = null)
    {
        if (is_null($namespace)) {
            self::getInstance()->reset();
            self::$_globalContainer = null;
            
            foreach (self::$_namespacedContainers as $_container) {
                $_container->reset();
            }
            self::$_namespacedContainers = array();
        } else {
            self::getInstance($namespace)->reset();
        }
    }
    
    /**
     * Gets a value from the container instance for the specified namespace
     *
     * @param string $key
     * @param string $namespace OPTIONAL The namespace of the container to access
     * @return mixed
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function get($key, $namespace = null)
    {
        return self::getInstance($namespace)->get($key);
    }
    
    /**
     * Sets a value in the container instance for the specified namespace
     *
     * @param string $key
     * @param mixed $value
     * @param string $namespace OPTIONAL The namespace of the container to update
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function set($key, $value, $namespace = null)
    {
        self::getInstance($namespace)->set($key, $value);
    }
    
    /**
     * Returns whether or not a key exists in the container instance for the specified namespace
     *
     * @param string $key
     * @param string $namespace OPTIONAL The namespace of the container to check
     * @return bool
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function has($key, $namespace = null)
    {
        return self::getInstance($namespace)->has($key);
    }
    
    /**
     * Gets the container instance for the specified namespace
     *
     * @param string $namespace OPTIONAL
     * @return Ampersand_Registry_Container 
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function getInstance($namespace = null)
    {
        if (is_null($namespace)) {
            if (is_null(self::$_globalContainer)) {
                self::$_globalContainer = new Ampersand_Registry_Container();
            }
                
            return self::$_globalContainer;
        }
        
        if (!array_key_exists($namespace, self::$_namespacedContainers)) {
            new Ampersand_Registry_Container($namespace);
        }
        
        return self::$_namespacedContainers[$namespace];
    }
    
    /**
     * Stores a static reference to the provided namespaced container instance 
     *
     * @param Ampersand_Registry_Container $container
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function addInstance(Ampersand_Registry_Container $container)
    {
        if ($namespace = $container->getNamespace()) {
            self::$_namespacedContainers[$namespace] = $container;
        }
    }
}