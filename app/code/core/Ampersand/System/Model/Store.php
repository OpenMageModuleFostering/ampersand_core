<?php
class Ampersand_System_Model_Store extends Mage_Core_Model_Store
{
    /**
     * Shortname of this class
     * 
     * @var string $_modelName
     */
    protected $_modelName = 'core/store';
    
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
    protected $_idField = 'store_id';
    
    /**
     * Required fields for saving this object.
     * If a default value is allowed then provide a non-null value
     *
     * @var array $_requiredFields
     */
    protected $_requiredFields = array(
        'name' => null,
        'code' => null,
        'group_id' => null,
        'website_id' => null,
        'is_active' => '0',
        'is_default' => '0',
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
        'website_id',
    );
    
    /**
     * By setting the ID of a Store, we attempt to load it. If we are unable
     * to load the Store, unset the id field as we want a new Sroup to be created.
     *
     * @param type $id int
     * @return Ampersand_System_Model_Store 
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
     * If not already set, retrieve the default website/group/store 
     * combination and return the group id for that store.
     *
     * @return int 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getGroupId()
    {
        if (!$groupId = $this->getData('group_id')) {
            if ($defaultStoreView = Mage::helper('ampersand_system/store')->getDefaultStoreView()) {
                $groupId = $defaultStoreView->getGroupId();
            }
            $this->setGroupId($groupId);
        
            if (!$websiteId = parent::getData('website_id')) {
                $group = Mage::app()->getGroup($groupId);
                parent::setData('website_id', $group->getWebsiteId());
            }
        }
        
        return $groupId;
    }
    
    /**
     * Set the website id to associate this Group with
     *
     * @param mixed $website Varien_Object, website id or website code
     * @return Ampersand_System_Model_Store
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setWebsiteId($website)
    {
        if (!is_object($website)) {
            $website = Mage::app()->getWebsite($website);
        }
        
        parent::setData('website_id', $website->getId());
        
        if ((!$groupId = parent::getData('group_id')) || (!in_array($groupId, $website->getGroupIds()))) {
            parent::setData('group_id', $website->getDefaultGroup()->getId());
        }
        
        return $this;
    }
    
    /**
     * If not already set, retrieve the group for this store and
     * return the website id for that group.
     *
     * @return int 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getWebsiteId()
    {
        if (!$websiteId = $this->getData('website_id')) {
            $websiteId = $this->getGroup()->getWebsiteId();
            parent::setData('website_id', $websiteId);
        }

        return $websiteId;
    }
    
    /**
     * Ensure we force the method use, rather than setData, where appropriate
     *
     * @param mixed $key
     * @param mixed $value
     * @return Ampersand_System_Model_Store
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
     * @return Ampersand_System_Model_Store
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
     * @return Ampersand_System_Model_Store
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
     * @return Ampersand_System_Model_Store
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