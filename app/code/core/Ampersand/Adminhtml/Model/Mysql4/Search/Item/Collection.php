<?php
class Ampersand_Adminhtml_Model_Mysql4_Search_Item_Collection
    extends Ampersand_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ampersand_adminhtml/search_item');
    }
}