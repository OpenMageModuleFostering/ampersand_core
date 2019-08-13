<?php
class Ampersand_Catalog_Helper_Product_Attribute extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve attribute by code.
     *
     * @param string $code
     * @return Mage_Eav_Model_Entity_Attribute_Abstract 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getAttribute($code)
    {
        return Mage::getModel('catalog/product')
            ->getResource()
            ->getAttribute($code);
    }
    
    /**
     * Retreive attribute option id by value.
     *
     * @param mixed $attribute
     * @param string $value
     * @return int 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getAttributeOptionId($attribute, $value)
    {
        if (!$attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $attribute = $this->getAttribute($attribute);
        }
        
        return $attribute->getSource()->getOptionId($value);
    }
    
    /**
     * Retrieve attribute set by code.
     *
     * @param string $code
     * @return Mage_Eav_Model_Entity_Attribute_Set 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getAttributeSet($code)
    {
        $attributeSet = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($this->getEntityTypeId('catalog_product'))
            ->addFieldToFilter('attribute_set_name', $code)
            ->getFirstItem();
        
        return $attributeSet;
    }
    
    /**
     * Retrieve entity type id by code.
     * 
     * @param string $code
     * @return int
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getEntityTypeId($code)
    {
        return Mage::getModel('eav/entity_type')->loadByCode($code)->getId();
    }
}