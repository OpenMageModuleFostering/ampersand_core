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
class Ampersand_Pairs implements Iterator
{
    protected $_keys = array();
    protected $_values = array();

    protected $_position = 0;

    public function __construct(array $keys = array(), array $values = array())
    {
        if (count($keys) !== count($values)) {
            throw new Ampersand_Exception('Values array and keys array are not the same size');
        }

        foreach ($keys as $_key) {
            $this->addPair($_key, current($values));

            next($values);
        }
    }

    public function addPair($key, $value = null)
    {
        if (is_array($key)) {
            $this->_validatePair($key);
            list($key, $value) = $key;
        }

        $this->_validateKey($key);

        $this->_keys[] = $key;
        $this->_values[] = $value;

        return $this;
    }

    protected function _validatePair(array $pair)
    {
        if (count($pair) !== 2) {
            throw new Ampersand_Exception('Pair must be an array with two elements (key, value)');
        }
    }

    protected function _validateKey($key)
    {
        if (!is_int($key) && !is_string($key)) {
            throw new Ampersand_Exception('Key values must be one either int or string');
        }
    }

    public function toArray()
    {
        if ($this->_keys) {
            return array_combine($this->_keys, $this->_values);
        }

        return array();
    }

    public function count()
    {
        return count($this->_keys);
    }

    /**
     * Return the current element
     *
     * @return mixed Can return any type.
     */
    public function current()
    {
        if ($this->valid()) {
            return $this->_values[$this->_position];
        }

        return false;
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * Return the key of the current element
     *
     * @return scalar scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        return $this->_keys[$this->_position];
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return array_key_exists($this->_position, $this->_values);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_position = 0;
    }
}