<?php
class Ampersand_Adminhtml_Block_System_Config_Renderer_Hidden
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::render($element);
        
        return preg_replace('/^(\<tr id="[^"]+")\>/', '$1 style="display:none">', $html);
    }
}