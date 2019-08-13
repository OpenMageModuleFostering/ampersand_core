<?php
class Ampersand_System_Model_Cms_Block extends Mage_Cms_Model_Block
{
    /**
     * Shortname of this class
     * 
     * @var string $_modelName
     */
    protected $_modelName = 'cms/block';
    
    /**
     * Field to load by when checking if already exists
     *
     * @var string $_loadField
     */
    protected $_loadField = 'identifier';
    
    /**
     * Database column name of the id field
     *
     * @var string $_idField
     */
    protected $_idField = 'block_id';
    
    /**
     * Required fields for saving this object.
     * If a default value is allowed then provide a non-null value
     *
     * @var array $_requiredFields
     */
    protected $_requiredFields = array(
        'title' => null,
        'identifier' => null,
        'stores' => array('0' => '0'),
        'is_active' => '1',
        'content' => '&nbsp;',
    );
    
    /**
     * By setting the ID of a Block, we attempt to load it. If we are unable
     * to load the Block, unset the id field as we want a new Sroup to be created.
     *
     * @param type $id int
     * @return Ampersand_System_Model_Cms_Block 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setId($id)
    {
        parent::setData($this->_idField, $id);
        Mage::helper('ampersand_system')->loadIfExists($this, $this->_modelName, $this->_idField);
        
        if (!$this->getIsObjectLoaded()) {
            parent::unsData($this->_idField);
        }
        
        return $this;
    }
    
    /**
     * Load if exists, validate and save the object
     *
     * @return Mage_Core_Model_Website
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _beforeSave()
    {
        Mage::helper('ampersand_system')->loadIfExists($this, $this->_modelName, $this->_loadField);
        Mage::helper('ampersand_system')->validate($this, $this->_requiredFields);

        return parent::_beforeSave();
    }
}