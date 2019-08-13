<?php
class Ampersand_System_Model_Config extends Mage_Core_Model_Abstract
{
    /**
     * Required fields for saving this object.
     * If a default value is allowed then provide a non-null value
     *
     * @var array $_requiredFields
     */
    protected $_requiredFields = array(
        'scope' => null,
        'scope_id' => null,
        'scope_code' => null,
    );

    /**
     * If these methods are called with setXxx, addXxx, setData(xxx, xxx) etc.
     * setDataUsingMethod() will always be forced.
     *
     * @var array $_forceSetDataUsingMethod
     */
    protected $_forceSetDataUsingMethod = array(
        'default',
        'website',
        'store',
        'node',
    );

    /**
     * Scope of config item to be saved
     *
     * @var string $_scope
     */
    protected $_scope;
    
    /**
     * For store and website scope, this value should be the store or website id
     *
     * @var int $_scopeId
     */
    protected $_scopeId;
    
    /**
     * For store and website scope, this value should be the store or website code
     *
     * @var int $_scopeCode
     */
    protected $_scopeCode;
    
    /**
     * Config nodes to be updated
     *
     * @var array $_nodes
     */
    protected $_nodes = array();
    
    /**
     * Default scope
     */
    const SCOPE_DEFAULT = 'default';
    
    /**
     * Website specific scope
     */
    const SCOPE_WEBSITE = 'websites';

    /**
     * Store specific scope
     */
    const SCOPE_STORE = 'stores';
    
    /**
     * Default scope id
     */
    const DEFAULT_SCOPE_ID = 0;
    
    /**
     * Default scope code
     */
    const DEFAULT_SCOPE_CODE = '';
    
    /**
     * Set the scope for default config settings
     *
     * @param bool OPTIONAL Friendly syntax for setData('default', true);
     * @return Ampersand_System_Model_Config 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setDefault($foobar = true)
    {
        $this->_scope = self::SCOPE_DEFAULT;
        $this->_scopeId = self::DEFAULT_SCOPE_ID;
        $this->_scopeCode = self::DEFAULT_SCOPE_CODE;
        
        return $this;
    }
    
    /**
     * Set the scope for website specific config settings
     *
     * @param mixed $website Varien_Object, website id or website code
     * @return Ampersand_System_Model_Config
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setWebsite($website)
    {
        if (!is_object($website)) {
            $website = Mage::app()->getWebsite($website);
        }
        $websiteId = $website->getId();
        $websiteCode = $website->getCode();

        if ($websiteId !== self::DEFAULT_SCOPE_ID) {
            $this->_scope = self::SCOPE_WEBSITE;
            $this->_scopeId = $websiteId;
            $this->_scopeCode = $websiteCode;
        }

        return $this;
    }
    
    /**
     * Set the scope for store specific config settings
     *
     * @param mixed $store Varien_Object, store id or store code
     * @return Ampersand_System_Model_Config
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setStore($store)
    {
        if (!is_object($store)) {
            $store = Mage::app()->getStore($store);
        }
        $storeId = $store->getId();
        $storeCode = $store->getCode();
        
        if ($storeId !== self::DEFAULT_SCOPE_ID) {
            $this->_scope = self::SCOPE_STORE;
            $this->_scopeId = $storeId;
            $this->_scopeCode = $storeCode;
        }
        
        return $this;
    }
    
    /**
     * Add a new config node to be updated. Multiple values can be set using an
     * array of path => value pairs. If the value is set to null, then the 
     * corresponding config node will be deleted for that scope.
     *
     * @param mixed $path Array of path => value or single path of config setting
     * @param type $value New value of config setting
     * @return Ampersand_System_Model_Config 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setNode($path, $value = null)
    {
        if (is_array($path)) {
            foreach ($path as $_path => $_value) {
                $this->setNode($_path, $_value);
            }
        } else {
            $path = trim($path, ' /');
            $this->_nodes[$path] = $value;
        }
        
        return $this;
    }
    
    /**
     * Retrieve config node value by $path. If no value is found for the current scope
     * the value in the parent scope will be returned. eg. website or default config value.
     *
     * @param string $path Path of config setting to retrieve
     * @return string
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getNode($path)
    {
        $path = trim($path, ' /');
        
        $value = Mage::getConfig()->getNode(
            $path,
            $this->getScope(),
            $this->getScopeCode()
        );
        
        return $value ? (string)$value : null;
    }
    
    /**
     * Retrieve config node value by $path for the the current scope only. If no value 
     * is found in this scope no value will be returned, regardless of parent scope values.
     *
     * @param string $path Path of config setting to retrieve
     * @return string
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getNodeInScope($path)
    {
        $config = Mage::getResourceModel('core/config');
        $connection = $config->getReadConnection();
        $select = $connection->select()
            ->from($config->getMainTable())
            ->where('path=?', $path)
            ->where('scope=?', $this->getScope())
            ->where('scope_id=?', $this->getScopeId());
        $row = $connection->fetchRow($select);
        return (is_array($row)) ? $row['value'] : null;
    }
    
    /**
     * Retrieve the current scope
     *
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getScope()
    {
        if (!$this->_scope) {
            $this->setDefault();
        }
        
        return $this->_scope;
    }
    
    /**
     * Retrieve the current scope id
     *
     * @return int 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getScopeId()
    {
        if (!$this->_scopeId) {
            $this->setDefault();
        }
        
        return $this->_scopeId;
    }
    
    /**
     * Retrieve the current scope code
     *
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getScopeCode()
    {
        if (!$this->_scopeCode) {
            $this->setDefault();
        }
        
        return $this->_scopeCode;
    }
    
    /**
     * Ensure we force the method use, rather than setData, where appropriate
     *
     * @param mixed $key
     * @param mixed $value
     * @return Ampersand_System_Model_Config
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
     * @return Ampersand_System_Model_Config
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
     * Update the config settings
     *
     * @return Ampersand_System_Model_Config
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function save($reinit = true, $useBackend = false)
    {
        Mage::helper('ampersand_system')->validate($this, $this->_requiredFields);
        
        foreach ($this->_nodes as $_path => $_value) {
            $config = Mage::getModel('core/config');
            if (is_null($_value)) {
                $config->deleteConfig($_path, $this->getScope(), $this->getScopeId());
            } else {
                if (!$useBackend || !$this->_saveWithClassInstance($_path, $_value)) {
                    $config->saveConfig($_path, $_value, $this->getScope(), $this->getScopeId());
                }
            }
        }
        
        if ($reinit) {
            $this->_reinit();
        }

        return $this;
    }
    
    /**
     * Attempt to save the config node using the backend model, if specified. This resolves
     * issues with values like passwords which need to be encrypted before being saved.
     *
     * @param string $path
     * @param string $value
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function _saveWithClassInstance($path, $value)
    {
        $configSections = Mage::getSingleton('adminhtml/config')->getSections();
        if (!$configSections) {
            return false;
        }
        
        $pathParts = explode('/', $path);
        $backendModelNode = $configSections->xpath("//sections/{$pathParts[0]}/groups/{$pathParts[1]}/fields/{$pathParts[2]}/backend_model");
        if (!is_array($backendModelNode)) {
            return false;
        }
        
        $backendModelAlias = array_shift($backendModelNode);
        $backendModelAlias = (string)$backendModelAlias;
        if ($backendModelAlias == '') {
            return false;
        }
        
        $classInstance = Mage::getModel($backendModelAlias);
        if (!$classInstance instanceof Mage_Core_Model_Config_Data) {
            return false;
        }
        
        $node = $classInstance->getCollection()
            ->addFieldToFilter('scope', $this->getScope())
            ->addFieldToFilter('scope_id', $this->getScopeId())
            ->addFieldToFilter('path', $path)
            ->getFirstItem();
        
        if ($nodeId = $node->getId()) {
            $classInstance->load($nodeId);
        }
        
        $classInstance->addData(array(
            'scope' => $this->getScope(),
            'scope_id' => $this->getScopeId(),
            'path' => $path,
            'value' => $value,
        ));
        
        $classInstance->save();
        
        return true;
    }
    
    /**
     * Reinit config so latest changes immediately available
     *
     * @return Ampersand_System_Model_Config 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _reinit()
    {
        switch ($this->getScope())
        {
            case self::SCOPE_DEFAULT:
            case self::SCOPE_WEBSITE:
                $config = Mage::getConfig();
                $config->reinit($config->getOptions());
                break;
            
            case self::SCOPE_STORE:
                Mage::app()->getStore($this->getScopeId())->resetConfig();
                break;
        }
        
        return $this;
    }
}