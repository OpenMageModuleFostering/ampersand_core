<?php
class Ampersand_System_Model_Category
{
    /**
     * Imported CSV file
     *
     * @var array $_importData
     */
    protected $_importData = array();

    /**
     * RootCategoryId for the selected store
     *
     * @var array $_rootCategoryId
     */
    protected $_rootCategoryId;

    /**
     * Currently selected store
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Set the scope for store specific config settings
     *
     * @param mixed $store Varien_Object, store id or store code
     * @return Ampersand_System_Model_Category
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    public function setStore($store)
    {
        if (!is_object($store)) {
            $store = Mage::app()->getStore($store);
        }

        $this->_store = $store;

        return $this;
    }

    /**
     * Set the scope for store specific config settings
     *
     * @param mixed $store Varien_Object, store id or store code
     * @return Ampersand_System_Model_Category
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    public function setDefault()
    {
        $this->setStore('default');
        return $this;
    }

    /**
     * Imports the CSV file of Categories formatted with "Name","url_key","parent_url_key","featured"
     * The CSV file must be located under $moduleName/data/
     * @param string $moduleName
     * @param string $filepath
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    public function import($moduleName, $filepath)
    {
        if(!$this->_store){
            throw new Exception('Store not set');
        }

        $this->_lockIndexer();
        // Auto detect line endings
        ini_set('auto_detect_line_endings', true);

        // Import CSV
        $parser = new Varien_File_Csv();
        $filePath = Mage::getModuleDir('', $moduleName) . DS. 'data' . DS . $filepath;
        $this->_importData = $parser->getData($filePath);

        $this->_rootCategoryId = $this->_store->getRootCategoryId();
        
        $currentStoreId = Mage::app()->getStore()->getId();
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->_deleteAllCategories();
        $this->_createNewCategories();
        $this->_makeAllCategoriesAnchors();
        $this->_reIndex();
        Mage::app()->getStore()->setId($currentStoreId);
    }

    /**
     * Locks the indexer prior to deleting and creating categories
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _lockIndexer()
    {
        Mage::getSingleton('index/indexer')->lockIndexer();
    }

    /**
     * Delete all categories below the root category
     */
    protected function _deleteAllCategories()
    {
        $rootCategory = Mage::getModel('catalog/category')->load($this->_rootCategoryId);
        $query = "DELETE FROM `catalog_category_product` WHERE `category_id` IN (SELECT entity_id  FROM `catalog_category_entity` WHERE `path` LIKE '{$rootCategory->getPath()}/%')";
        Mage::getResourceModel('catalog/category')->getWriteConnection()->query($query);

        $query = "DELETE FROM catalog_category_entity where `path` LIKE '{$rootCategory->getPath()}/%'";
        Mage::getResourceModel('catalog/category')->getWriteConnection()->query($query);
    }

    /**
     * Creates New Categories based on those retrieved from the uploaded CSV file
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _createNewCategories()
    {
        $csvCategories = array();
        $parentCategories = array();
        
        foreach ($this->_importData as $_line) {
            $name = isset($_line[0]) ? $_line[0] : '';
            $urlKey = isset($_line[1]) ? $_line[1] : '';
            $parentUrlKey = isset($_line[2]) ? $_line[2] : '';
            $featured = isset($_line[3]) ? $_line[3] : '0';

            $name = trim($name);
            $urlKey = strtolower(preg_replace('/[^a-zA-Z0-9\\\]/', '-', str_replace(array(' & ', ' &', '& '), '-and-', trim($urlKey))));
            $parentUrlKey = strtolower(preg_replace('/[^a-zA-Z0-9\\\]/', '-', str_replace(array(' & ', ' &', '& '), '-and-', trim($parentUrlKey))));

            if (!$name || !$urlKey) {
                //echo 'Missing name or url key: ' . print_r($_line, true) . '<br />';
                continue;
            }
            if (!$parentUrlKey) {
                $parentId = $this->_rootCategoryId;
                
            } else {
                if (array_key_exists($parentUrlKey, $parentCategories)) {
                    $parentId = $parentCategories[$parentUrlKey];
                } else {
                    //echo 'Parent Category does not exist: ' . print_r($_line, true) . '<br />';
                    continue;
                }
            }
            $parentCategories[$urlKey] = $this->_createCategory(array(
                'name' => $name,
                'url_key' => $urlKey,
                'featured' => $featured,
            ), $parentId);
        }

    }

    /**
     * Creates an individual category
     *
     * @param array $categoryData
     * @param int $parentId
     * @return categoryId
     */
    protected function _createCategory($categoryData, $parentId)
    {
        // Default required fields
        $categoryDefaultData = array(
            'name' => 'Name',
            'url_key' => 'url-key',
            'available_sort_by' => 'position',
            'default_sort_by' => 'position',
            'include_in_menu' => '1',
            'is_active' => '1',
        );
                
        $categoryDataObject = new stdClass();
        $categoryData = array_merge($categoryDefaultData, $categoryData);
        foreach ($categoryData as $_field => &$_value) {
            // Make sure we have values set for the required attributes
            if (array_key_exists($_field, $categoryDefaultData) && !$_value) {
                $_value = $categoryDefaultData[$_field];
            }
            $categoryDataObject->{$_field} = $_value;
        }
        try {
            $categoryId = Mage::getModel('catalog/category_api_v2')
                ->create($parentId, $categoryDataObject, null)
            ;
            
        } catch (Exception $e) {
            Mage::log($e->getMessage() . PHP_EOL . print_r($categoryDataObject, true) . PHP_EOL . $e->getTraceAsString());
            die('Unable to create category' . PHP_EOL . print_r($categoryDataObject, true));
        }
        return $categoryId;
    }

    /**
     * Make all categories anchors
     */
    protected function _makeAllCategoriesAnchors()
    {
        $categoryIds = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToFilter('level', array('gteq' => '2'))
            ->getAllIds()
        ;

        foreach ($categoryIds as $_categoryId) {
            $category = Mage::getModel('catalog/category')->load($_categoryId);
            $category->setIsAnchor(true);
            $category->save();
        }
    }

    /**
     * Unlock and reIndex categories
     */
    protected function _reIndex()
    {
        Mage::getSingleton('index/indexer')->unlockIndexer()->indexEvents();
    }
}