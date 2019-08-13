<?php
class Ampersand_Adminhtml_Model_Search_Item extends Ampersand_Core_Model_Abstract
{
    const DEFAULT_GET_PARAM = 'search_item';
    
    protected function _construct()
    {
        $this->_init('ampersand_adminhtml/search_item');
    }
    
    /**
     * @return null|Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getCurrentSearchItem()
    {
        $searchItemId = Mage::app()->getFrontController()->getRequest()
            ->getParam(self::DEFAULT_GET_PARAM);
        
        if (!$searchItemId) {
            return null;
        }
        
        $searchItem = Mage::getModel('ampersand_adminhtml/search_item')
            ->load($searchItemId);
        
        if (!$searchItem->getId()) {
            return null;
        }
        
        if (!$searchItem->getIsActive()) {
            $searchItem->refreshData();
        }
        
        if (!$searchItem->getIsActive()) {
            return null;
        }
        
        return $searchItem;
    }
    
    /**
     * @return null|Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getPrevSearchItem()
    {
        return $this->getSibling($this->getPrevCollectionItemId());
    }
    
    /**
     * @return null|Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getNextSearchItem()
    {
        return $this->getSibling($this->getNextCollectionItemId());
    }
    
    /**
     * @return null|Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getSibling($collectionItemId)
    {
        $item = $this->getSiblingCollection()
            ->addFieldToFilter('collection_item_id', $collectionItemId)
            ->setPageSize(1)
            ->getFirstItem();
        
        if (!$item->getId()) {
            return null;
        }
        
        return $item;
    }
    
    /**
     * @return Ampersand_Adminhtml_Model_Mysql4_Search_Item_Collection
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getSiblingCollection()
    {
        return $this->getCollection()->addFieldToFilter('search_id', $this->getSearchId());
    }
    
    /**
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getIsActive()
    {
        return true;
        
        if (!$expiresAt = $this->getExpiresAt()) {
            return false;
        }
        
        $expiresAtObject = new Zend_Date($expiresAt, 'y-MM-dd HH:mm:ss');
        $currentTimeObject = new Zend_Date();
        
        return $expiresAtObject->isEarlier($currentTimeObject);
    }
    
    /**
     * @param mixed $collectionItemId
     * @param object $collectionItem
     * @param Varien_Data_Collection_Db $collection 
     * @return Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function fromCollectionItem($collectionItemId, $collectionItem,
        Varien_Data_Collection_Db $collection
    ) {
        $collectionItems = $collection->getItems();
        $collectionItemIds = array_keys($collectionItems);
        
        $itemIndex = array_search($collectionItemId, $collectionItemIds);
        
        $prevCollectionItemId = $this->_getPrevCollectionItemId($itemIndex, $collectionItemIds);
        $nextCollectionItemId = $this->_getNextCollectionItemId($itemIndex, $collectionItemIds);
        
        $this->setId($this->_getSearchItemId());
        $this->setCollectionItemId($collectionItemId);
        $this->setPrevCollectionItemId($prevCollectionItemId);
        $this->setNextCollectionItemId($nextCollectionItemId);
        $this->setPosition(1 + $itemIndex);
        
        return $this;
    }
    
    /**
     * @return Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function refreshData()
    {
        $search = $this->getSearch();
        $collectionItemId = $this->getCollectionItemId();
        
        $itemSelectSql = $search->setSingleItemSelectSql();
        $itemSelectSql = $this->_prepareSingleItemSelectSql($itemSelectSql, $collectionItemId);
        $collectionItemData = $this->getResource()->getReadConnection()->fetchRow($itemSelectSql);
        
        return $this;
    }
    
    /**
     * @param string $sql
     * @param mixed $collectionItemId 
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _prepareSingleItemSelectSql($sql, $collectionItemId)
    {
        return $sql;
    }
    
    /**
     * @param string $sql
     * @param mixed $collectionItemId 
     * @param array $orderValues
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _prepareNeighbourItemsSelectSql($sql, $collectionItemId, $orderValues)
    {
        return $sql;
    }
    
    /**
     * @return Ampersand_Adminhtml_Model_Search
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getSearch()
    {
        if (!$this->hasData('search')) {
            $search = Mage::getModel('ampersand_adminhtml/search')
                ->load($this->getSearchId());
            $this->setData('search', $search);
        }
        
        return $this->getData('search');
    }
    
    /**
     * @param int $itemIndex
     * @param array $collectionItemIds 
     * @return mixed
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getPrevCollectionItemId($itemIndex, array $collectionItemIds)
    {
        if (!$itemIndex) {
            return null;
        }
        
        return $collectionItemIds[$itemIndex - 1];
    }
    
    /**
     * @param int $itemIndex
     * @param array $collectionItemIds 
     * @return mixed
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getNextCollectionItemId($itemIndex, array $collectionItemIds)
    {
        if (1 + $itemIndex >= count($collectionItemIds)) {
            return null;
        }
        
        return $collectionItemIds[1 + $itemIndex];
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getSearchItemId()
    {
        return base64_encode(substr(microtime(true), 3) . mt_rand(100, 999));
    }
}