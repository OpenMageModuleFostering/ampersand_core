<?php
class Ampersand_Core_Model_Mysql4_Value extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;
    
    protected function _construct()
    {
        $this->_init('ampersand_core/value', 'name');
    }
}