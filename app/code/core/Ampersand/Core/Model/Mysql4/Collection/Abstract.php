<?php
/**
 * Ampersand IT Magento Suite
 *
 * @category    Ampersand_Magento
 * @package     Ampersand_Core
 * @subpackage  Model
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Magento
 * @package     Ampersand_Core
 * @subpackage  Model
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
abstract class Ampersand_Core_Model_Mysql4_Collection_Abstract
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_preserveColumnsOnCount = false;
    
    public function joinLeft($table, $cond, $cols = '*')
    {
        if (!isset($this->_joinedTables[$table])) {
            $this->getSelect()->joinLeft(array($table => $this->getTable($table)), $cond, $cols);
            $this->_joinedTables[$table] = true;
        }

        return $this;
    }

    public function getPairs($columnName)
    {
        $this->load();

        $pairs = array();
        foreach ($this->getItems() as $_item) {
            $pairs[$this->_getItemId($_item)] = $_item->getData($columnName);
        }

        return $pairs;
    }

    public function fetchPairs($columnName, $forceFetch = false)
    {
        if (!$forceFetch && $this->isLoaded()) {
            return $this->getPairs($columnName);
        }

        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);

        $idFieldName = $this->getNewEmptyItem()->getIdFieldName();
        $columnName = $this->_getMappedField($columnName);

        $select->columns(array($idFieldName, $columnName), 'main_table');

        return $this->getConnection()->fetchPairs($select);
    }

    public function addFieldToFilterHaving($field, $condition = null)
    {
        $this->_preserveColumnsOnCount = true;
        
        $field = $this->_getMappedField($field);
        $this->_select->having($this->_getConditionSql($field, $condition));

        return $this;
    }

    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);

        if ($this->_preserveColumnsOnCount) {
            $countSelect = $this->getConnection()->select()
                ->from($countSelect, 'COUNT(*)');
        } else {
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->columns('COUNT(*)');
        }

        return $countSelect;
    }

    public function delete()
    {
        foreach ($this as $_item) {
            $_item->delete();
        }

        return $this;
    }

    /**
     * Add filter to Map
     * 
     * This is missing from Mage 1.4.0.1 CE - taken from Varien_Data_Collection_Db
     *
     * @param string $filter
     * @param string $alias
     * @param string $group default 'fields'
     * @return Varien_Data_Collection_Db
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addFilterToMap($filter, $alias, $group = 'fields')
    {
        if (method_exists(get_parent_class(), 'addFilterToMap')) {
            return parent::addFilterToMap($filter, $alias, $group);
        }
        
        if (is_null($this->_map)) {
            $this->_map = array($group => array());
        } else if(is_null($this->_map[$group])) {
            $this->_map[$group] = array();
        }
        $this->_map[$group][$filter] = $alias;

        return $this;
    }
    
    /**
     * @param int $collectionSize OPTIONAL Pass the total number of records in the search to prevent
     * an additional database query
     * @return Ampersand_Adminhtml_Model_Search
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getSearch($collectionSize = null)
    {
        return Mage::getModel('ampersand_adminhtml/search')
            ->fromCollection($this, $collectionSize)
            ->save();
    }
}