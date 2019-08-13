<?php
class Ampersand_Adminhtml_Block_Widget_Container extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * @return Ampersand_Adminhtml_Block_Widget_Container
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addSearchScrollButtons($itemName = null)
    {
        if (!$searchItem = $this->getSearchItem()) {
            return $this;
        }
        
        $position = $searchItem->getPosition();
        $searchSize = $searchItem->getSearch()->getSize();
        
        if ($itemName) {
            $label = Mage::helper('ampersand_adminhtml')->__(
                '%s %s of %s', $itemName, $position, $searchSize
            );
        } else {
            $label = Mage::helper('ampersand_adminhtml')->__(
                '%s of %s', $position, $searchSize
            );
        }
            
        $this->_addButton('search_position', array(
            'label'     => $label,
            'onclick'   => 'window.location.reload();',
        ));
        
        if ($prevUrl = $this->getPrevSearchItemUrl()) {
            $this->_addButton('search_prev', array(
                'label'     => '&laquo;',
                'onclick'   => 'setLocation(\'' . $prevUrl . '\')',
            ));
        } else {
            $this->_addButton('search_prev', array(
                'label'     => '&laquo;',
                'disabled'  => true,
            ));
        }
        
        if ($nextUrl = $this->getNextSearchItemUrl()) {
            $this->_addButton('search_next', array(
                'label'     => '&raquo;',
                'onclick'   => 'setLocation(\'' . $nextUrl . '\')',
            ));
        } else {
            $this->_addButton('search_next', array(
                'label'     => '&raquo;',
                'disabled'  => true,
            ));
        }
        
        return $this;
    }
    
   /**
     * @return null|string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
    */
    public function getPrevSearchItemUrl()
    {
        if (!$prevItem = $this->getSearchItem()->getPrevSearchItem()) {
            return null;
        }
        
        return $this->getSearchItemUrl($prevItem);
    }
    
    /**
     * @return null|string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getNextSearchItemUrl()
    {
        if (!$nextItem = $this->getSearchItem()->getNextSearchItem()) {
            return null;
        }
        
        return $this->getSearchItemUrl($nextItem);
    }
    
    /**
     * @return null|string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getSearchItemUrl(Ampersand_Adminhtml_Model_Search_Item $searchItem)
    {
        return null;
    }
    
    /**
     * @return null|Ampersand_Adminhtml_Model_Search_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getSearchItem()
    {
        if (!$this->hasData('search_item')) {
            $searchItem = Mage::getSingleton('ampersand_adminhtml/search_item')
                ->getCurrentSearchItem();
            $this->setData('search_item', $searchItem);
        }
        
        return $this->getData('search_item');
    }
}