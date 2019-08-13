<?php
class Ampersand_Catalog_Helper_Category extends Mage_Core_Helper_Abstract
{
    /**
     * Top category level.
     */
    const TOP_CATEGORY_LEVEL = 2;
    
    /**
     * Cache of categories by url key.
     * We store like this just in case there are restrictions over array keys.
     *
     * @var array $_category
     */
    protected $_categoryUrlKeys = array();
    protected $_categoriesByUrlKey = array();
    
    /**
     * Cache of root categories.
     * 
     * @var array $_rootCategories
     */
    protected $_rootCategories = array();
    
    /**
     * Retreive the URL for a category by url key(s) and store.
     *
     * @param string $urlKey
     * @param mixed $store
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getUrlByUrlKeys($urlKeys, $store = null)
    {
        return $this->getCategoryByUrlKeys($urlKeys, $store)->getUrl();
    }
    
    /**
     * Retrieve a category by url key(s) and store.
     *
     * @param string $urlKeys
     * @param mixed $store
     * @param bool $useCache
     * @return Mage_Catalog_Model_Category 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getCategoryByUrlKeys($urlKeys, $store = null, $useCache = true)
    {
        $store = Mage::app()->getStore($store);
        $storeId = $store->getId();
        
        if ($useCache && array_key_exists($storeId, $this->_categoryUrlKeys)
                && (($key = array_search($urlKeys, $this->_categoryUrlKeys[$storeId])) !== FALSE)) {
            return $this->_categoriesByUrlKey[$storeId][$key];
        }
        
        $this->_categoryUrlKeys[$storeId][] = $urlKeys;
        $key = array_search($urlKeys, $this->_categoryUrlKeys[$storeId]);
        $this->_categoriesByUrlKey[$storeId][$key] = null;
        
        $parentId = null;
        $urlKeyParts = explode('/', $urlKeys);
        foreach ($urlKeyParts as $_urlKey) {
            $_urlKey = trim($_urlKey);
            if (empty($_urlKey)) {
                continue;
            }
            
            $filters = array(
                'url_key' => $_urlKey,
            );

            if (is_null($parentId)) {
                $filters['level'] = self::TOP_CATEGORY_LEVEL;
            } else {
                $filters['parent_id'] = $parentId;
            }
            
            $category = $this->getCategoryByFilters($filters, $store);
            if ($category->getId()) {
                $parentId = $category->getId();
            } else {
                // unable to find the category
                return null;
            }
        }
        
        $this->_categoriesByUrlKey[$storeId][$key] = $category
            ->setStoreId($storeId)
            ->load($category->getId());
        
        return $this->_categoriesByUrlKey[$storeId][$key];
    }
    
    /**
     * Retreive a category by attribute filters.
     *
     * @param array $filters
     * @return Mage_Catalog_Model_Category 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getCategoryByFilters(array $filters, $store = null)
    {
        $store = Mage::app()->getStore($store);
        
        $rootCategory = $this->getRootCategory($store);
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToFilter('path', array('like' => "{$rootCategory->getPath()}%"));
        
        foreach ($filters as $_key => $_value) {
            $collection->addAttributeToFilter($_key, $_value);
        }
        
        return $collection->getFirstItem();
    }
    
    /**
     * Retrieve the root category for a store.
     *
     * @param mixed $store
     * @return Mage_Catalog_Model_Category 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getRootCategory($store = null)
    {
        $store = Mage::app()->getStore($store);
        $storeId = $store->getId();
        
        if (!array_key_exists($storeId, $this->_rootCategories)) {
            $this->_rootCategories[$storeId] = Mage::getModel('catalog/category')
                ->setStoreId($store->getId())
                ->load($store->getRootCategoryId());
        }
        
        return $this->_rootCategories[$storeId];
    }
    
    /**
     * Clear category cache.
     *
     * @param mixed $store
     * @return Ampersand_Catalog_Helper_Category 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function clearCache($store = null)
    {
        if (is_null($store)) {
            $this->_categoryUrlKeys = array();
            $this->_categoriesByUrlKey = array();
            $this->_rootCategories = array();
        } else {
            $storeId = Mage::app()->getStore($store)->getId();
            $this->_categoryUrlKeys[$storeId] = array();
            $this->_categoriesByUrlKey[$storeId] = array();
            $this->_rootCategories[$storeId] = array();
        }
        
        return $this;
    }
}