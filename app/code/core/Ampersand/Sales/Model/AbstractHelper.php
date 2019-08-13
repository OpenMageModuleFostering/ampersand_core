<?php
abstract class Ampersand_Sales_Model_AbstractHelper extends Varien_Object
{
    /**
     * Product id to sku mapping for this object.
     *
     * @var array $_productIdSkuMap 
     */
    protected $_productIdSkuMap;
    
    /**
     * Generate product id to sku mapping for this object.
     *
     * @param array $items
     * @return array 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getProductIdSkuMap($items)
    {
        if (is_null($this->_productIdSkuMap)) {
            // first get all the product ids we need
            $productIds = array();
            foreach ($items as $_item) {
                $productIds[] = $_item->getProductId();
                if ($_item->getHasChildren()) {
                    foreach ($_item->getChildrenItems() as $__child) {
                        $productIds[] = $__child->getProductId();
                    }
                }
            }

            // fetch the skus from the database
            $productResourceSingleton = Mage::getResourceSingleton('catalog/product');
            if (method_exists($productResourceSingleton, 'getProductsSku')) {
                $productsSkuResult = $productResourceSingleton->getProductsSku($productIds);
            } else {
                $productsSkuResult = array();
                $productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToFilter('entity_id', $productIds);
                foreach ($productCollection as $_product) {
                    $productsSkuResult[] = array(
                        'entity_id' => $_product->getId(),
                        'sku' => $_product->getSku(),
                    );
                }
            }
            
            // convert the result to a productId => sku array
            $productIdSkuData = array();
            foreach ($productsSkuResult as $_skuData) {
                $productIdSkuData[$_skuData['entity_id']] = $_skuData['sku'];
            }

            $this->_productIdSkuMap = $productIdSkuData;
        }
        
        return $this->_productIdSkuMap;
    }
}