<?php
class Ampersand_Adminhtml_Model_Mysql4_Search_Item extends Ampersand_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;
    
    protected function _construct()
    {
        $this->_init('ampersand_adminhtml/search_item', 'search_item_id');
    }
}