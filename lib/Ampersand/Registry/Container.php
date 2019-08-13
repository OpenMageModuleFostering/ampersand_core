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
class Ampersand_Registry_Container
{
    /** @var string */
    protected $_namespace;
    /** @var array */
    protected $_values = array();
    
    /**
     * Stores the provided namespace against this container and adds this container instance to
     * the global registry
     *
     * @param string $namespace OPTIONAL
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public function __construct($namespace = null)
    {
        if (!is_null($namespace)) {
            $this->_namespace = $namespace;
        }
        
        if (!is_null($this->_namespace)) {
            Ampersand_Registry::addInstance($this);
        }
    }
    
    /**
     * Returns the namespace of this registry container
     *
     * @return string
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }
    
    /**
     * Resets this container to its original state. Values are wiped, etc.
     *
     * @return Ampersand_Registry_Container 
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public function reset()
    {
        $this->_values = array();
        
        return $this;
    }
    
    /**
     * Gets a value from this container
     *
     * @param string $key
     * @return mixed 
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->_values)) {
            return null;
        }
        
        return $this->_values[$key];
    }
    
    /**
     * Sets a value in this container
     *
     * @param string $key
     * @param mixed $value
     * @return Ampersand_Registry_Container 
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public function set($key, $value)
    {
        $this->_values[$key] = $value;
        
        return $this;
    }
    
    /**
     * Returns whether or not a key exists in this container
     *
     * @param string $key
     * @return bool
     * @author Josh Di fabio <josh.difabio@ampersandit.co.uk>
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_values);
    }
}