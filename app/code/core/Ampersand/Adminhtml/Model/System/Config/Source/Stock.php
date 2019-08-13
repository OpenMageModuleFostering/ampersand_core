<?php
class Ampersand_Adminhtml_Model_System_Config_Source_Stock
{
    /** @var null|array */
    protected $_optionArray;
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function toOptionArray()
    {
        return Mage::getSingleton('ampersand_adminhtml/system_config_source_stock')
            ->getOptionArray();
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getOptionArray()
    {
        if (is_null($this->_optionArray)) {
            $this->_optionArray = $this->_getOptionArray();
        }
        
        return $this->_optionArray;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getOptionArray()
    {
        $stockResource = Mage::getResourceSingleton('cataloginventory/stock');
        
        $select = $stockResource->getReadConnection()->select()
            ->from($stockResource->getMainTable(), array(
                'value' => $stockResource->getIdFieldName(),
                'label' => 'stock_name',
            ))
            ->order('stock_name', 'asc');
        
        return $stockResource->getReadConnection()->fetchAll($select);
    }
}