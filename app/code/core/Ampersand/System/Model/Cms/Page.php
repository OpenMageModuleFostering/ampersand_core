<?php
class Ampersand_System_Model_Cms_Page extends Mage_Cms_Model_Page
{
    /**
     * Shortname of this class
     * 
     * @var string $_modelName
     */
    protected $_modelName = 'cms/page';
    
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
    protected $_idField = 'page_id';
    
    /**
     * Required fields for saving this object.
     * If a default value is allowed then provide a non-null value
     *
     * @var array $_requiredFields
     */
    protected $_requiredFields = array(
        'title' => null,
        'identifier' => null,
        'is_active' => '1',
        'content_heading' => '',
        'content' => '&nbsp;',
        'root_template' => 'empty',
        'under_version_control' => '0',
        'stores' => array(0),
    );
    
    /**
     * By setting the ID of a Page, we attempt to load it. If we are unable
     * to load the Page, unset the id field as we want a new Sroup to be created.
     *
     * @param type $id int
     * @return Ampersand_System_Model_Cms_Page 
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