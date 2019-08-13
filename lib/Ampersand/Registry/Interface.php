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
interface Ampersand_Registry_Interface
{
    /**
     * Resets the registry
     *
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function reset();
    
    /**
     * Gets a value from the container instance
     *
     * @param string $key
     * @return mixed
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function get($key);
    
    /**
     * Sets a value in the container instance
     *
     * @param string $key
     * @param mixed $value
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function set($key, $value);
    
    /**
     * Returns whether or not a key exists in the container instance
     *
     * @param string $key
     * @return bool
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function has($key);
}