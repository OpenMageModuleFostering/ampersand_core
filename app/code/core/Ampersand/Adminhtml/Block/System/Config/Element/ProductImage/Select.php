<?php
/**
 * Ampersand IT Magento Suite
 *
 * @category    Ampersand_Magento
 * @package     Ampersand_Adminhtml
 * @subpackage  Block
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Magento
 * @package     Ampersand_Adminhtml
 * @subpackage  Block
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Adminhtml_Block_System_Config_Element_ProductImage_Select
    extends Mage_Core_Block_Html_Select
{
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function getOptions()
    {
        if (!$this->_options) {
            $this->_options = $this->_getSource()->toOptionArray();
        }

        return $this->_options;
    }

    protected function _getSource()
    {
        return Mage::getSingleton(
            'ampersand_adminhtml/system_config_source_productImageAttributes'
        );
    }
}