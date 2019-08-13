<?php
class Ampersand_Core_Model_Mysql4_Collection extends Ampersand_Core_Model_Mysql4_Collection_Abstract
{
    public function fromCollection(Varien_Data_Collection_Db $collection)
    {
        $this->fromSelect($collection->getSelect());
        $this->setItemObjectClass($collection->_itemObjectClass);
        
        return $this;
    }
    
    public function fromSelect(Zend_Db_Select $select)
    {
        $this->_select = $select;
        
        return $this;
    }
}