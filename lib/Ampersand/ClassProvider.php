<?php
/**
 * Ampersand IT Library
 *
 * @category    Ampersand_Library
 * @package     Ampersand
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Library
 * @package     Ampersand
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_ClassProvider
{
    protected $_classes = array();

    public function getObject($alias)
    {
        if (!$alias) {
            throw new Ampersand_Exception('No class alias specified');
        }
        if (!$class = $this->getClass($alias)) {
            throw new Ampersand_Exception("No class found for alias '$alias'");
        }

        return new $class;
    }
    
    public function getSingleton($alias)
    {
        $class = $this->getClass($alias);
        
        if (!$singleton = Ampersand_Registry::get($class, '__singleton')) {
            $singleton = new $class;
            Ampersand_Registry::set($class, $singleton, '__singleton');
        }
        
        return $singleton;
    }

    public function getClass($alias)
    {
        if (array_key_exists($alias, $this->_classes)) {
            return $this->_classes[$alias];
        }

        return null;
    }

    public function addClass($alias, $class)
    {
        $this->_classes[$alias] = $class;

        return $this;
    }
}