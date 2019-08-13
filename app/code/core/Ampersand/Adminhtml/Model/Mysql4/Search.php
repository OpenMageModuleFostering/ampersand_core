<?php
class Ampersand_Adminhtml_Model_Mysql4_Search extends Ampersand_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('ampersand_adminhtml/search', 'entity_id');
    }
    
    /**
     * @param Mage_Core_Model_Abstract $object 
     * @return Ampersand_Adminhtml_Model_Mysql4_Search
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $now = now();
        if ($object->isObjectNew()) {
            $object->setCreatedAt($now);
        }
        $object->setUpdatedAt($now);
        
        parent::_beforeSave($object);
        
        return $this;
    }
}