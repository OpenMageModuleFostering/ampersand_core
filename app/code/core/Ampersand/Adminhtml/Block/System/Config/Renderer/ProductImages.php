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
class Ampersand_Adminhtml_Block_System_Config_Renderer_ProductImages
    extends Ampersand_Adminhtml_Block_System_Config_Renderer_ArrayAbstract
{
    /** @var Ampersand_Adminhtml_Block_System_Config_Element_ProductImage_Attribute */
    protected $_attributeRenderer;
    /** @var Ampersand_Adminhtml_Block_System_Config_Element_ProductImage_UseFrame */
    protected $_useFrameRenderer;

    /**
     * Prepares block to be rendered
     *
     * @return Ampersand_Adminhtml_Block_System_Config_Renderer_ProductImages
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _prepareToRender()
    {
        $this->addColumn('code', array(
            'label' => Mage::helper('ampersand_adminhtml')->__('Reference Code'),
            'style' => 'width:100px',
        ));
        $this->addColumn('attribute', array(
            'label'     => Mage::helper('ampersand_adminhtml')->__('Magento Image Name'),
            'renderer'  => $this->_getAttributeRenderer(),
        ));
        $this->addColumn('width', array(
            'label' => Mage::helper('ampersand_adminhtml')->__('Width'),
            'style' => 'width:55px',
        ));
        $this->addColumn('height', array(
            'label' => Mage::helper('ampersand_adminhtml')->__('Height'),
            'style' => 'width:55px',
        ));
        $this->addColumn('use_frame', array(
            'label' => Mage::helper('ampersand_adminhtml')->__('Use Frame'),
            'renderer'  => $this->_getUseFrameRenderer(),
        ));
        $this->addColumn('background', array(
            'label' => Mage::helper('ampersand_adminhtml')->__('Frame Colour'),
            'style' => 'width:55px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('ampersand_adminhtml')->__('Add Image');

        return $this;
    }

    /**
     * Retrieves renderer for product image attribute field
     *
     * @return Ampersand_Adminhtml_Block_System_Config_Element_ProductImage_Attribute
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _getAttributeRenderer()
    {
        if (is_null($this->_attributeRenderer)) {
            $this->_attributeRenderer = $this->getLayout()->createBlock(
                'ampersand_adminhtml/system_config_element_productImage_attribute', '',
                array('is_render_to_js_template' => true)
            );

            $this->_attributeRenderer->setExtraParams('style="width:130px"');
        }

        return $this->_attributeRenderer;
    }

    /**
     * Retrieves renderer for product image attribute field
     *
     * @return Ampersand_Adminhtml_Block_System_Config_Element_ProductImage_Attribute
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _getUseFrameRenderer()
    {
        if (is_null($this->_useFrameRenderer)) {
            $this->_useFrameRenderer = $this->getLayout()->createBlock(
                'ampersand_adminhtml/system_config_element_productImage_useFrame', '',
                array('is_render_to_js_template' => true)
            );

            $this->_useFrameRenderer->setExtraParams('style="width:55px"');
        }

        return $this->_useFrameRenderer;
    }

    /**
     * Select correct option in attribute select
     *
     * @param Varien_Object
     * @return Ampersand_Adminhtml_Block_System_Config_Renderer_ProductImages
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getAttributeRenderer()->calcOptionHash(
                $row->getData('attribute')
            ),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_' . $this->_getUseFrameRenderer()->calcOptionHash(
                $row->getData('use_frame')
            ),
            'selected="selected"'
        );

        return $this;
    }
}