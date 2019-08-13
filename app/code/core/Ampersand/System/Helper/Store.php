<?php
class Ampersand_System_Helper_Store extends Mage_Core_Helper_Abstract
{
    /**
     * Flag to ensure reinitStores is only run once
     *
     * @var bool $_reinitStoreFlag
     */
    protected $_reinitStoreFlag = false;
    
    /**
     * Cached default store view
     *
     * @var Mage_Core_Model_Store $_defaultStoreView
     */
    protected $_defaultStoreView;
    
    /**
     * If Mage::app()->_initStores() has not been run any attempts to retrieve arrays of
     * stores or websites, eg. Mage::app()->getWebsites() will return empty arrays.
     *
     * @return Ampersand_System_Helper_Data 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function reinitStores()
    {
        if (!$this->_reinitStoreFlag) {
            $this->_reinitStoreFlag = true;
            Mage::app()->reinitStores();
        }
        
        return $this;
    }
    
    /**
     * Retrieve the default store view by first re-initialising stores
     * to ensure Mage_Core_Model_App::_websites is populated.
     *
     * @return Mage_Core_Model_Store 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getDefaultStoreView()
    {
        if (!$this->_defaultStoreView) {
            $this->reinitStores();
            $this->_defaultStoreView = Mage::app()->getDefaultStoreView();
        }
        
        return $this->_defaultStoreView;
    }
}