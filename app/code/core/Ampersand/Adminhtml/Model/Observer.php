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
class Ampersand_Adminhtml_Model_Observer
{
    protected $_gridBlocks = array();
    protected $_layoutHandles;
    protected $_gridUpdateConfigs = array();
    
    /**
     * Handle adminhtml_catalog_product_edit_prepare_form event
     * 
     * @param Varien_Event_Observer $observer
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function handleProductEditForm(Varien_Event_Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        /* @var $form Varien_Data_Form */
        
        $this->_applyCustomRenderers($form);
    }
    
    /**
     * Handle catalog_product_prepare_save event
     *
     * @param Varien_Event_Observer $observer 
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function prepareProductForSave(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /* @var $product Mage_Catalog_Model_Product */
        
        if ($useConfigValues = $product->getData('ampersand_adminhtml_use_config')) {
            foreach ($useConfigValues as $_useConfigAttributeCode) {
                $product->setData($_useConfigAttributeCode, 0);
            }
        }
        
        $request = $observer->getEvent()->getRequest();
        /* @var $request = Mage_Core_Controller_Request_Http */
        
        if ($useDefaults = $request->getPost('use_default')) {
            foreach ($useDefaults as $_useDefaultAttributeCode) {
                $_attribute = $product->getResource()->getAttribute($_useDefaultAttributeCode);
                if ($_attribute && ($_attribute->getFrontend() instanceof Ampersand_Adminhtml_Model_Entity_Attribute_Frontend_ConfigurableInterface)) {
                    $product->setData('use_config_' . $_useDefaultAttributeCode, false);
                }
            }
        }
    }
    
    /**
     * @param Varien_Data_Form_Abstract $formElement 
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _applyCustomRenderers(Varien_Data_Form_Abstract $formElement)
    {
        if ($attribute = $formElement->getEntityAttribute()) {
            if ($attribute->getFrontend() instanceof Ampersand_Adminhtml_Model_Entity_Attribute_Frontend_ConfigurableInterface) {
                $formElement->setRenderer(Mage::app()->getLayout()->createBlock(
                    'ampersand_adminhtml/catalog_form_renderer_fieldset_configurableElement'
                ));
            }
        }
        
        foreach ($formElement->getElements() as $_childElement) {
            $this->_applyCustomRenderers($_childElement);
        }
    }

    /**
     * Handle core_block_abstract_prepare_layout_after event
     * 
     * @param Varien_Event_Observer $observer
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function handleNewBlock(Varien_Event_Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Widget_Grid) {
            $this->_gridBlocks[$block->getNameInLayout()] = $block;
            $this->_prepareGrid($block);
        } else {
            switch ($block->getType()) {
                case 'adminhtml/widget_grid_column':
                    $this->_executeGridUpdateCallbacks($block, 'column');
                    break;
                case 'adminhtml/widget_grid_massaction':
                    $this->_executeGridUpdateCallbacks($block, 'massaction');
                    break;
                default:
                    if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Column) {
                        $this->_executeGridUpdateCallbacks($block, 'column');
                    } else if ($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract) {
                        $this->_executeGridUpdateCallbacks($block, 'massaction');
                    }
                    break;
            }
        }
    }

    /**
     * @param mixed $gridBlock 
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _prepareGrid($gridBlock)
    {
        
    }

    /**
     * @param mixed $targetBlock
     * @param string $type
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _executeGridUpdateCallbacks($targetBlock, $type)
    {
        $gridBlock = $this->_getContainingGrid();

        if (!$callbacks = $this->_getGridUpdateCallbacks($gridBlock, $type)) {
            return;
        }

        foreach ($callbacks as $_callback) {
            call_user_func($_callback, $targetBlock, $gridBlock);
        }
    }

    /**
     * @param mixed $gridBlock
     * @param string $type
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getGridUpdateCallbacks($gridBlock, $type)
    {
        $callbacks = array();

        foreach ($this->_getGridUpdateConfigs($type) as $_config) {
            if ($_config->grid_name
                    && (string) $_config->grid_name != $gridBlock->getNameInLayout()) {
                continue;
            }
            if ($_config->grid_type
                    && (string) $_config->grid_type != $gridBlock->getType()) {
                continue;
            }
            if ($_config->layout_handle
                    && !$this->_isLayoutHandleActive((string) $_config->layout_handle)) {
                continue;
            }

            switch ($_config->callback->type) {
                case 'singleton':
                    $callbacks[] = array(
                        Mage::getSingleton((string) $_config->callback->class),
                        (string) $_config->callback->method
                    );
                    break;
                case 'object':
                case 'model':
                    $callbacks[] = array(
                        Mage::getModel((string) $_config->callback->class),
                        (string) $_config->callback->method
                    );
                    break;
                default:
                    $callbacks[] = array(
                        (string) $_config->callback->class_name,
                        (string) $_config->callback->method,
                    );
                    break;
            }
        }

        return $callbacks;
    }

    /**
     * @param string $type
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getGridUpdateConfigs($type)
    {
        if (!array_key_exists($type, $this->_gridUpdateConfigs)) {
            $config = Mage::getConfig()->getNode("adminhtml/grid_updates/$type");
            $this->_gridUpdateConfigs[$type] = is_object($config) ? $config->children() : array();
        }

        return $this->_gridUpdateConfigs[$type];
    }

    /**
     * @param string $handle
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _isLayoutHandleActive($handle)
    {
        if (is_null($this->_layoutHandles)) {
            $this->_layoutHandles = Mage::app()->getLayout()->getUpdate()->getHandles();
        }

        return in_array($handle, $this->_layoutHandles);
    }

    /**
     * @return null|Mage_Adminhtml_Block_Widget_Grid 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getContainingGrid()
    {
        if (1 == count($this->_gridBlocks)) {
            return reset($this->_gridBlocks);
        }

        if (count($this->_gridBlocks)) {
            foreach (debug_backtrace() as $_item) {
                if (array_key_exists('object', $_item)
                        && $_item['object'] instanceof Mage_Adminhtml_Block_Widget_Grid) {
                    return $_item['object'];
                }
            }
        }

        return null;
    }
}