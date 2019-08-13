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
abstract class Ampersand_Adminhtml_Block_System_Config_Renderer_ArrayAbstract
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_columns = array(true); // workaround for 1.4.0.1 ce
    
    /**
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _construct()
    {
        array_shift($this->_columns);
        parent::_construct();
    }
    
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = parent::render($element);

        if (!trim($element->getLabel())) {
            $xmlObject = Ampersand_Xml::factory("<html>$html</html>");
            $row = $xmlObject->getChild('tr');
            $row->removeChild('td#0');
            $row->getChild('td#0')->setAttribute('colspan', 2);

            $html = $xmlObject->getInnerXml();
        }
        
        $html .= $this->_getInitSelectValuesJs($element);

        return $html;
    }

    /**
     * Render block HTML
     * 
     * Included as workaround for 1.4.0.1 ce
     *
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _toHtml()
    {
        if (!$this->_isPreparedToRender) {
            $this->_prepareToRender();
            $this->_isPreparedToRender = true;
        }
        if (empty($this->_columns)) {
            throw new Exception('At least one column must be defined.');
        }
        
        return parent::_toHtml();
    }
    
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $columnName
     * @param array $options
     * @return Mage_Core_Block_Html_Select
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getSelectRenderer(Varien_Data_Form_Element_Abstract $element, $columnName,
        array $options
    ) {
        $renderer = $this->getLayout()->createBlock(
            'core/html_select', '',
            array('is_render_to_js_template' => true)
        );

        $renderer->setOptions($options);
        $renderer->setName($this->_getElementName($element, '#{_id}', $columnName));
        
        return $renderer;
    }
    
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $rowId
     * @param string $columnName 
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getElementName(Varien_Data_Form_Element_Abstract $element, $rowId, $columnName)
    {
        return "{$element->getName()}[$rowId][$columnName]";
    }
    
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getInitSelectValuesJs(Varien_Data_Form_Element_Abstract $element)
    {
        $updateTypeValueJs = "\n<script type=\"text/javascript\">";
        
        $updateTypeValuesJsLines = array();
        
        $rows = $this->getArrayRows();
        
        if (!is_array($rows)) {
            return '';
        }
        
        foreach ($rows as $_rowId => $_rowData) {
            foreach ($this->_columns as $__columnName => $__column) {
                if (!$__column['renderer'] instanceof Mage_Core_Block_Html_Select
                    || !isset($_rowData[$__columnName])
                ) {
                    continue;
                }
                
                $__selectName = $this->_getElementName($element, $_rowId, $__columnName);
                $updateTypeValuesJsLines[] = "$('$_rowId').down('[name=\"$__selectName\"]')"
                    . ".setValue('{$_rowData[$__columnName]}');";
            }
        }

        $updateTypeValueJs .= implode("\n", $updateTypeValuesJsLines);
        
        return $updateTypeValueJs . "\n</script>";
    }

    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getHtmlId()
    {
        $element = $this->getElement();
        
        if (!$element->hasHtmlId()) {
            $element->setHtmlId('_' . uniqid());
        }
        
        return $element->getData('html_id');
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getArrayRows()
    {
        $arrayRows = array();
        
        foreach (parent::getArrayRows() as $_oldRowId => $_row) {
            $_rowId = "{$this->getHtmlId()}_row_{$_oldRowId}";
            $arrayRows[$_rowId] = $_row->setData('_id', $_rowId);
        }
        
        return $arrayRows;
    }
}