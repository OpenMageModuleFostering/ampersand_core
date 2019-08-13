<?php
class Ampersand_Catalog_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * Cache of products by SKU.
     *
     * @var array $_product
     */
    protected $_productsBySku = array();
    
    /**
     * Retreive the URL for a product by SKU and store.
     *
     * @param string $sku
     * @param mixed $store
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getUrlBySku($sku, $store = null)
    {
        return $this->getProductBySku($sku, $store)->getProductUrl();
    }
    
    /**
     * Retreive a product by SKU and store.
     *
     * @param string $sku
     * @param mixed $store
     * @return Mage_Catalog_Model_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getProductBySku($sku, $store = null)
    {
        $store = Mage::app()->getStore($store);
        
        $storeId = $store->getId();
        $productId = Mage::getSingleton('catalog/product')->getIdBySku($sku);
        
        if (!array_key_exists($storeId, $this->_productsBySku)
                || !array_key_exists($productId, $this->_productsBySku[$storeId])) {
            $this->_productsBySku[$storeId][$productId] = Mage::getModel('catalog/product')
                ->setStoreId($storeId)
                ->load($productId);
        }
        
        return $this->_productsBySku[$storeId][$productId];
    }
    
    /**
     * Clear product cache.
     *
     * @param mixed $store
     * @return Ampersand_Catalog_Helper_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function clearCache($store = null)
    {
        if (is_null($store)) {
            $this->_productsBySku = array();
        } else {
            $storeId = Mage::app()->getStore($store)->getId();
            $this->_productsBySku[$storeId] = array();
        }
        
        return $this;
    }
}