<?php
class Ampersand_Stack extends Ampersand_Object
{
    /**
     * Key to be used to set data against the object.
     *
     * @var string $_stackKey
     */
    protected $_stackKey = 'stack';
    
    /**
     * Define the key to be used to set data against the object.
     *
     * @param string $key
     * @return Ampersand_Stack 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setStackKey($key)
    {
        $this->_stackKey = $key;
        
        return $this;
    }
    
    /**
     * Retrieve the key to be used to set data against the object.
     *
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getStackKey()
    {
        if ($this->_stackKey == '') {
            throw new Exception('Stack key cannot be empty.');
        }
        
        return $this->_stackKey;
    }
    
    /**
     * Add data to the stack.
     *
     * @param mixed $value
     * @param string $key
     * @return Ampersand_Stack 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function addToStack($value, $key = null)
    {
        $stack = $this->getStack();
        
        if (!is_null($key)) {
            $stack[$key] = $value;
        } else {
            $stack[] = $value;
        }
        
        $this->setData($this->getStackKey(), $stack);
        
        return $this;
    }
    
    /**
     * Set the stack data.
     *
     * @param array $value
     * @return Ampersand_Stack 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setStack($value)
    {
        $this->setData($this->getStackKey(), $value);
        
        return $this;
    }
    
    /**
     * Empty the stack data.
     *
     * @return Ampersand_Stack 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function emptyStack()
    {
        $this->setData($this->getStackKey(), array());
        
        return $this;
    }
    
    /**
     * Retrieve the stack data.
     *
     * @return array 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getStack()
    {
        $stack = $this->getData($this->getStackKey());
        if (!is_array($stack)) {
            $stack = array();
        }
        
        return $stack;
    }
}