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
class Ampersand_Adminhtml_Model_System_Config_Source_Store
{
    public function toOptionArray()
    {
        $websites = array();

        foreach ($this->_getStoresStructure() as $_key => $_website) {
            $websites[$_key] = array(
                'label' => $_website['label'],
                'value' => array(),
            );

            foreach ($_website['children'] as $__group) {
                $websites[$_key]['value'] = array_merge(
                    $websites[$_key]['value'],
                    $__group['children']
                );
            }
        }

        return $websites;
    }

    protected function _getStoresStructure()
    {
        return Mage::getSingleton('adminhtml/system_store')->getStoresStructure();
    }
}