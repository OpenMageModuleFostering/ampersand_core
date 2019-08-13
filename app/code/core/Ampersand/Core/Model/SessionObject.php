<?php
/**
 * Semi-implemented for use in Monocore Meal Plan. Untested.
 * 
 * The idea being that you could extend this abstract and have an object that
 * saved its values in the session rather than in a database, perhaps for some temporary
 * storage for example a multi-part process that you only want to persist once complete.
 */
abstract class Ampersand_Core_Model_SessionObject extends Mage_Core_Model_Abstract
{
    protected $_sessionModel = 'core/session';
    
    protected $_sessionDataKey = null;
    
    public function getData($key = '', $index = null)
    {
        $data = $this->_getSession()->getData($this->_getSessionDataKey());
        
        if ($key == '') {
            return $data;
        }
        
        return array_key_exists($key, $data) ? $data[$key] : null;
    }
    
    public function setData($key, $value=null)
    {
        $data = $this->getData();
        
        if (!is_array($data)) {
            $data = array();
        }
        
        if (is_array($key)) {
            $data = $key;
        } else {
            $data[$key] = $value;
        }
        
        $this->_getSession()->setData($this->_getSessionDataKey(), $data);
        
        return $this;
    }
    
    protected function _getSessionModel()
    {
        $sessionModel = $this->_sessionModel;
        
        if (is_null($sessionModel)) {
            Mage::throwException('Session model must be defined.');
        }
        
        return $sessionModel;
    }
    
    protected function _getSessionDataKey()
    {
        $sessionDataKey = $this->_sessionDataKey;
        
        if (is_null($sessionDataKey)) {
            Mage::throwException('Session data key must be defined.');
        }
        
        return $sessionDataKey;
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton($this->_getSessionModel());
    }
}