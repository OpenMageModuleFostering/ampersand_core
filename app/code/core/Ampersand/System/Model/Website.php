<?php
class Ampersand_System_Model_Website extends Mage_Core_Model_Website
{
    /**
     * Shortname of this class
     * 
     * @var string $_modelName
     */
    protected $_modelName = 'core/website';
    
    /**
     * Field to load by when checking if already exists
     *
     * @var string $_loadField
     */
    protected $_loadField = 'code';
    
    /**
     * Database column name of the id field
     *
     * @var string $_idField
     */
    protected $_idField = 'website_id';
    
    /**
     * Required fields for saving this object.
     * If a default value is allowed then provide a non-null value
     *
     * @var array $_requiredFields
     */
    protected $_requiredFields = array(
        'name' => null,
        'code' => null,
        'sort_order' => '0',
    );

    /**
     * If these methods are called with setXxx, addXxx, setData(xxx, xxx) etc.
     * setDataUsingMethod() will always be forced.
     *
     * @var array $_forceSetDataUsingMethod
     */
    protected $_forceSetDataUsingMethod = array(
        'id',
    );
    
    /**
     * By setting the ID of a Website, we attempt to load it. If we are unable
     * to load the Website, unset the id field as we want a new Website to be created.
     *
     * @param type $id int
     * @return Ampersand_System_Model_Website 
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
     * Ensure we force the method use, rather than setData, where appropriate
     *
     * @param mixed $key
     * @param mixed $value
     * @return Ampersand_System_Model_Website
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $_key => $_value) {
                $this->_setDataUsingMethod($_key, $_value);
            }
        } else {
            $this->_setDataUsingMethod($key, $value);
        }

        return $this;
    }

    /**
     * Ensure we force the method use, rather than setData, where appropriate
     *
     * @param string $key
     * @param mixed $value
     * @return Ampersand_System_Model_Website
     */
    protected function _setDataUsingMethod($key, $value = null)
    {
        if (in_array($key, $this->_forceSetDataUsingMethod)) {
            $this->setDataUsingMethod($key, $value);
        } else {
            parent::setData($key, $value);
        }

        return $this;
    }
    
    /**
     * Load if exists, validate and save the object
     *
     * @return Ampersand_System_Model_Website
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _beforeSave()
    {
        Mage::helper('ampersand_system')->loadIfExists($this, $this->_modelName, $this->_loadField);
        Mage::helper('ampersand_system')->validate($this, $this->_requiredFields);

        return parent::_beforeSave();
    }
    
    /**
     * Reinitstores required so new object available from Mage::app().
     *
     * @return Ampersand_System_Model_Website
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        
        if ($this->isObjectNew()) {
            Mage::app()->reinitStores();
        }
        
        return $this;
    }
}