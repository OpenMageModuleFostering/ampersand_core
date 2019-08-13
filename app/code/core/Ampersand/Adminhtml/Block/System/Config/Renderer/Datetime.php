<?php
class Ampersand_Adminhtml_Block_System_Config_Renderer_Datetime
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setFormat('yyyy-MM-dd HH:mm');
        $element->setTime(true);
        $element->setImage($this->getSkinUrl('images/grid-cal.gif'));
        
        return parent::render($element);
    }
}