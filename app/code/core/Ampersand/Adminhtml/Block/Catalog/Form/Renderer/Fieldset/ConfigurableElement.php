<?php
class Ampersand_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_ConfigurableElement
    extends Mage_Adminhtml_Block_Catalog_Form_Renderer_Fieldset_Element
{
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getElementHtml()
    {
        if ($this->getShouldUseConfig()) {
            $this->setValue($this->_getValueFromConfig());
        }
        
        return parent::getElementHtml() . $this->getUseConfigHtml();
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getUseConfigHtml()
    {
        $origTemplate = $this->_template;
        
        $this->_template = 'ampersand_adminhtml/catalog/form/renderer/element/use-config.phtml';
        
        $configHtml = $this->renderView();
        
        $this->_template = $origTemplate;
        
        return $configHtml;
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getUseConfigInputName()
    {
        $elementName = $this->getElement()->getName();
        $attributeCode = preg_quote($this->getAttributeCode());
        $useConfigAttributeCode = preg_quote($this->getUseConfigAttributeCode());
        
        return preg_replace('/(^|\[)' . $attributeCode . '(\]?).*$/', '$1' . $useConfigAttributeCode . '$2', $elementName);
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getUseConfigHiddenInputName()
    {
        $elementName = $this->getElement()->getName();
        $attributeCode = preg_quote($this->getAttributeCode());
        
        return preg_replace('/(^|\[)' . $attributeCode . '(\]?).*$/', '$1ampersand_adminhtml_use_config$2[]', $elementName);
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getUseConfigHiddenInputValue()
    {
        return $this->getUseConfigAttributeCode();
    }
    
    /**
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getShouldUseConfig()
    {
        if (!$this->getDataObject()->getId()) {
            return true;
        }
        
        if ($this->getDataObject()->getDataUsingMethod($this->getUseConfigAttributeCode())) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getUseConfigAttributeCode()
    {
        $elementName = $this->getElement()->getName();
        
        if (!preg_match('/\[(?P<attribute_code>[^[]+)\](\[\])*$/', $elementName, $matches)) {
            throw new Exception(sprintf(
                'Unable to determine attribute code from element name: "%s"', $elementName
            ));
        }
        
        return 'use_config_' . $matches['attribute_code'];
    }
    
    /**
     * @return null
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getValueFromConfig()
    {
        return null;
    }
}