<?php
class Ampersand_Adminhtml_Block_Widget_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function getSearchInstance()
    {
        if (is_null($this->_searchInstance)) {
            $this->_searchInstance = $this->getCollection()->getSearch();
        }
        
        return $this->_searchInstance;
    }
    
    public function getSearchItemId($collectionItemId)
    {
        return $this->getSearchInstance()->getSearchItem($collectionItemId)
            ->getSearchItemId();
    }
}