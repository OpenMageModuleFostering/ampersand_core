<?php
class Ampersand_Adminhtml_Model_Search extends Ampersand_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ampersand_adminhtml/search');
    }
    
    /**
     * @param Varien_Data_Collection_Db $collection
     * @param int $collectionSize OPTIONAL Pass the total number of records in the search to prevent
     * an additional database query
     * @return Ampersand_Adminhtml_Model_Search
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function fromCollection(Varien_Data_Collection_Db $collection, $collectionSize = null)
    {
        if (is_null($collectionSize)) {
            $collectionSize = $collection->getSize();
        }
        
        $this->setSize($collectionSize);
        
        $this->setExpiresAt($this->_getExpiresAt());
        
        $this->setSingleItemSelectSql(
            $this->_getSingleItemSelect($collection)->assemble()
        );
        
        $this->setNeighbourItemsSelectSql(
            $this->_getNeighbourItemsSelect($collection)->assemble()
        );
        
        $orderPart = $collection->getSelect()->getPart(Zend_Db_Select::ORDER); 
        if (!is_array($orderPart)) {
            $this->setOrderPart($orderPart);
        }
        
        $this->setSearchItems($this->_getSearchItems($collection));
        
        return $this;
    }
    
    /**
     * @param mixed $collectionItemId
     * @return null|Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getSearchItem($collectionItemId)
    {
        return $this->getData("search_items/$collectionItemId");
    }
    
    /**
     * @param Varien_Data_Collection_Db $collection 
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getSearchItems(Varien_Data_Collection_Db $collection)
    {
        $searchItems = array();
        
        foreach ($collection as $_collectionItemId => $_collectionItem) {
            $searchItems[$_collectionItemId] = Mage::getModel('ampersand_adminhtml/search_item')
                ->fromCollectionItem($_collectionItemId, $_collectionItem, $collection);
        }
        
        return $searchItems;
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getExpiresAt()
    {
        return date('Y-m-d H:i:s', time() + 30);
    }
    
    /**
     * @param Varien_Data_Collection_Db $collection 
     * @return Zend_Db_Select
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getSingleItemSelect(Varien_Data_Collection_Db $collection)
    {
        $select = clone $collection->getSelect();
        
        return $select;
    }
    
    /**
     * @param Varien_Data_Collection_Db $collection 
     * @return Zend_Db_Select
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getNeighbourItemsSelect(Varien_Data_Collection_Db $collection)
    {
        $select = clone $collection->getSelect();
        
        return $select;
    }
    
    /**
     * @return Ampersand_Adminhtml_Model_Search 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _afterSave()
    {
        if (!$this->getOrigData($this->getIdFieldName())) {
            foreach ($this->getSearchItems() as $_searchItem) {
                $_searchItem->setSearchId($this->getId())
                            ->setExpiresAt($this->getExpiresAt())
                            ->save();
            }
        }
        
        parent::_afterSave();
        
        return $this;
    }
}