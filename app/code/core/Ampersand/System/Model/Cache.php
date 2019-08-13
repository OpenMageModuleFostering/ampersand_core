<?php
class Ampersand_System_Model_Cache
{
    /**
     * Disable cache.
     *
     * @return Ampersand_System_Model_Cache
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function disableCache()
    {
        $options = $this->_getAllOptionsAsDisabled();
        $this->_getResourceModel()->saveAllOptions($options);
        
        $this->flushCache();
        
        return $this;
    }
    
    /**
     * Flush cache by following the behaviour of the 'Flush Cache Storage' button.
     *
     * @return Ampersand_System_Model_Cache
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function flushCache()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
            apc_clear_cache('user');
            apc_clear_cache('opcode');
        }
        
        Mage::dispatchEvent('adminhtml_cache_flush_all');
        Mage::app()->getCacheInstance()->flush();
        
        return $this;
    }
    
    /**
     * Retreive all cache options as an array with each value set to disabled.
     *
     * @return array
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getAllOptionsAsDisabled()
    {
        $options = $this->_getResourceModel()->getAllOptions();
        foreach ($options as $_option => &$_value) {
            $_value = '0';
        }
        
        return $options;
    }
    
    /**
     * Retrieve cache resource model.
     *
     * @return type Mage_Core_Model_Mysql4_Cache
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getResourceModel()
    {
        return Mage::getResourceSingleton('core/cache');
    }
}