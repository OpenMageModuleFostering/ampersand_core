<?php
class Ampersand_Api_Model_Catalog_Product_Attribute 
{
    public function getAttributeSet($code)
    {
        $attributeSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($this->getEntityTypeId('catalog_product'))
            ->addFieldToFilter('attribute_set_name', $code)
            ->getFirstItem();
        
        return $attributeSet;
    }
    
    public function getEntityTypeId($code)
    {
        return Mage::getModel('eav/entity_type')->loadByCode($code)->getId();
    }
    
    /**
     * These methods remain here for backwards compatability,
     * however they are more suited to Ampersand_Catalog_Helper_Product_Attribute.
     */
}