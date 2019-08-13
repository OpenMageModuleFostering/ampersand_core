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
class Ampersand_Adminhtml_Model_System_Config_Source_Category
{
    public function toOptionArray()
    {
        $tree = Mage::getResourceSingleton('catalog/category_tree')->load();
        
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->setStoreId(0)
            ->addAttributeToSelect('name');
        $tree->addCollectionData($collection, true);
        
        $rootNode = $tree->getNodeById(1);
        
        return $this->_nodeToOptionArray($rootNode);
    }
    
    protected function _nodeToOptionArray($node, $level = 0)
    {
        $options = array();
        
        foreach ($node->getChildren() as $_childNode) {
            $options[] = array(
                'value' => $_childNode->getId(),
                'label' => str_repeat('..', $level) . ' ' . $_childNode->getName(),
            );
            
            if ($_childNode->hasChildren()) {
                $options = array_merge($options, $this->_nodeToOptionArray($_childNode, $level + 1));
            }
        }
            
        return $options;
    }
}