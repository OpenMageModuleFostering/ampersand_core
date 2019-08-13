<?php
/**
 * Ampersand IT Magento Suite
 *
 * @category    Ampersand_Magento
 * @package     Ampersand_Adminhtml
 * @subpackage  Model
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Magento
 * @package     Ampersand_Adminhtml
 * @subpackage  Model
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Adminhtml_Model_System_Config_Source_ProductAttributeSets
{
    public function toOptionArray()
    {
        $options = array();

        $labels = $this->getPairs();

        foreach ($labels as $_attributeSetId => $_label) {
            $options[] = array(
                'value' => $_attributeSetId,
                'label' => $_label,
            );
        }

        return $options;
    }

    public function getPairs()
    {
        $attributeCollection = $this->_getAttributeSetCollection();
        
        $codes = $attributeCollection->getColumnValues('attribute_set_id');
        $labels = $attributeCollection->getColumnValues('attribute_set_name');
        
        $pairs = array();
        
        foreach (array_combine($codes, $labels) as $_code => $_label) {
            if (strlen($_code) && strlen($_label)) {
                $pairs[$_code] = $_label;
            }
        }
        
        return $pairs;
    }

    protected function _getAttributeSetCollection()
    {
        return Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($this->_getProductEntityTypeId());
    }
    
    protected function _getProductEntityTypeId()
    {
        return Mage::getResourceModel('catalog/product')->getTypeId();
    }
}