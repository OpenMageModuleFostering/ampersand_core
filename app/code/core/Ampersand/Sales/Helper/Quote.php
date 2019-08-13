<?php
class Ampersand_Sales_Helper_Quote extends Mage_Core_Helper_Abstract
{
    /**
     * Prepare the request object for adding a product to the quote.
     *
     * @param mixed $product
     * @param mixed $productData
     * @param mixed $childProduct
     * @param array $configurableAttributes
     * @return Ampersand_Object 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function prepareProductRequest($product, $productData, $childProduct = null, 
        $configurableAttributes = array())
    {
        // initialize request object
        $request = new Ampersand_Object();
        
        // initialize product
        $product = $this->_initProduct($product);
        
        // set the product data
        if (!is_array($productData)) {
            $productData = array(
                'qty' => $productData
            );
        }
        $request->addData($productData);
        
        // prepare any configurable attributes by provided child product
        if (!is_null($childProduct)) {
            $childProduct = $this->_initProduct($childProduct);
            $superAttributeData = $this->getSuperAttributeDataFromProducts($product, $childProduct);
            $request->setSuperAttribute($superAttributeData);
        }
        
        // prepare any configurable attributes by provided attribute values
        if (is_null($childProduct) && count($configurableAttributes) > 0) {
            $superAttributeData = $this->getSuperAttributeDataFromValues($configurableAttributes);
            $request->setSuperAttribute($superAttributeData);
        }

        return $request;
    }
    
    /**
     * Retrieve super attribute data for adding a configurable product to the basket,
     * having been provided with the configurable $product and simple $childProduct.
     *
     * @param mixed $product
     * @param mixed $childProduct
     * @return array 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getSuperAttributeDataFromProducts($product, $childProduct)
    {
        $product = $this->_initProduct($product);
        $childProduct = $this->_initProduct($childProduct);
        
        if (!$product->isConfigurable()) {
            return array();
        }
        
        $superAttributeData = array();
        
        foreach ($product->getTypeInstance()->getConfigurableAttributes() as $_attribute) {
            $_productAttribute = $_attribute->getProductAttribute();
            $_attributeId = $_productAttribute->getId();
            $_attributeValue = $childProduct->getData($_productAttribute->getAttributeCode());
            $superAttributeData[$_attributeId] = $_attributeValue;
        }
        
        return $superAttributeData;
    }
    
    /**
     * Retrieve super attribute data for adding a configurable product to the basket,
     * having been provided with an array of the attribute codes and values (not ids).
     *
     * @param array $configurableAttributes
     * @return array 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getSuperAttributeDataFromValues($configurableAttributes)
    {
        $superAttributeData = array();
        
        $attributeHelper = Mage::helper('ampersand_catalog/product_attribute');
        
        foreach ($configurableAttributes as $_code => $_value) {
            $_productAttribute = $attributeHelper->getAttribute($_code);
            $_attributeId = $_productAttribute->getId();
            $_attributeValue = $attributeHelper->getAttributeOptionId($_productAttribute, $_value);
            $superAttributeData[$_attributeId] = $_attributeValue;
        }
        
        return $superAttributeData;
    }
    
    /**
     * Retrive product object from provided product id or object.
     *
     * @param mixed $product
     * @return Mage_Catalog_Model_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _initProduct($product)
    {
        if (!$product instanceof Mage_Catalog_Model_Product) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        
        return $product;
    }
}