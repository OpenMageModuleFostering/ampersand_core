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
class Ampersand_Adminhtml_Model_System_Config_Source_ProductAttributes
{
    public function toOptionArray()
    {
        $options = array();

        $labels = $this->getPairs();

        foreach ($labels as $_attributeCode => $_label) {
            $options[] = array(
                'value' => $_attributeCode,
                'label' => $_label,
            );
        }

        return $options;
    }

    public function getPairs()
    {
        $attributeCollection = $this->_getAttributeCollection();
        
        $codes = $attributeCollection->getColumnValues('attribute_code');
        $labels = $attributeCollection->getColumnValues('frontend_label');
        
        $pairs = array();
        
        foreach (array_combine($codes, $labels) as $_code => $_label) {
            if (strlen($_code) && strlen($_label)) {
                $pairs[$_code] = $_label;
            }
        }
        
        return $pairs;
    }

    protected function _getAttributeCollection()
    {
        return Mage::getResourceModel('catalog/product_attribute_collection')
            ->setOrder('frontend_label', 'asc');
    }
}