<?php
class Ampersand_Core_Helper_Data extends Ampersand_Core_Helper_Abstract
{
    const CONFIG_PATH_PREFIX = '__AMPERSAND/';
    
    protected $_lockFiles = array();
    protected $_cachedValues = array();
    
    /**
     * @param string $string
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function prepareStringForHtmlRender($string)
    {
        $string = utf8_encode($string);
        $string = str_replace(' ', '<space/>', $string);
        $string = $this->htmlEscape($string, array('space'));
        $string = str_replace('<space/>', '&nbsp;', $string);
        $string = nl2br($string);

        return $string;
    }
    
    /**
     * @param string $key
     * @return bool Returns true on success or false on failure
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function lock($key)
    {
        if (!$file = $this->_getLockFile($key)) {
            return false;
        }
        
        return flock($file, LOCK_EX | LOCK_NB);
    }

    /**
     * @param string $key
     * @return bool Returns true on success or false on failure
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function unlock($key)
    {
        if (!$file = $this->_getLockFile($key)) {
            return false;
            
        }
        
        return flock($file, LOCK_UN);
    }
    
    /**
     * @param string $key
     * @return resource
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getLockFile($key)
    {
        if (!array_key_exists($key, $this->_lockFiles)) {
            $varDir = Mage::getConfig()->getVarDir('locks');
            $filePath = $varDir . DS . 'ampersand_' . md5($key) . '.lock';
            
            if (is_file($filePath)) {
                $this->_lockFiles[$key] = fopen($filePath, 'w');
            } else {
                $this->_lockFiles[$key] = fopen($filePath, 'x');
                
                $oldMask = umask(0);
                chmod($filePath, 0777);
                umask($oldMask);
            }
            
            fwrite($this->_lockFiles[$key], date('r'));
        }
        
        return $this->_lockFiles[$key];
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * @return Ampersand_Core_Helper_Data 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function saveValue($name, $value)
    {
        Mage::getModel('ampersand_core/value')
            ->setName($name)
            ->setValue($value)
            ->save();
        
        $this->_cachedValues[$name] = $value;
        
        return $this;
    }
    
    /**
     * @param string $name
     * @return null|string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function loadValue($name)
    {
        if (!in_array($name, $this->_cachedValues)) {
            $this->_cachedValues[$name] = Mage::getModel('ampersand_core/value')
                ->load($name)
                ->getValue();
        }
        
        return $this->_cachedValues[$name];
    }
    
    /**
     * @param string $name
     * @return Ampersand_Core_Helper_Data 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function deleteValue($name)
    {
        Mage::getModel('ampersand_core/value')
            ->load($name)
            ->delete();
    
        unset($this->_cachedValues[$name]);
        
        return $this;
    }
    
    /**
     * Retrieve whether the current request is secure.
     *
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function isSecure()
    {
        return Mage::app()->getStore()->isCurrentlySecure();
    }
}