<?php
/**
 * @example
$productApi = Mage::getSingleton('ampersand_api/catalog_product');

$productApi->create(array(
    'sku'                       => 'Configurable',
    'name'                      => 'Configurable Product',
    'type_id'                   => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
    'attribute_set_id'          => '4',
    'status'                    => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
    'configurable_attributes'   => array('size', 'color'),
    'images'                    => array('Configurable-1.jpg'),
));

$productApi->update(array(
    'sku'                       => 'Simple',
    'name'                      => 'Simple Product',
    'type_id'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
    'attribute_set_id'          => '4',
    'status'                    => Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
    'parent_sku'                => 'Configurable',
    'color'                     => '4',
    'size'                      => '6',
));
 */
class Ampersand_Api_Model_Catalog_Product
{
    /**
     * Flag to store whether we need to save the product or not.
     *
     * @var bool $_productHasChanges
     */
    protected $_productHasChanges;
    
    /**
     * Cache of product attributes by attribute set id.
     *
     * @var array $_productAttributes
     */
    protected $_productAttributes = array();
    
    /**
     * Required attributes for correct saving of products.
     *
     * @var array $_requiredAttributes
     */
    protected $_requiredAttributes = array(
        'sku',
        'name',
        'type_id',
        'attribute_set_id',
        'status',
    );
    
    /**
     * Proxy method to update().
     *
     * @param array $productData
     * @return Mage_Catalog_Model_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function create(array $productData)
    {
        return $this->update($productData);
    }
    
    /**
     * Create or update products with additional functionality like defining configurable
     * attributes, assigning simple products to configurables and assinging product images.
     *
     * @param array $productData
     * @return Mage_Catalog_Model_Product
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function update(array $productData)
    {
        $this->_setProductHasChanges(false);
        
        if (!array_key_exists('sku', $productData)) {
            Mage::throwException('Missing SKU');
        }
        
        $product = Mage::getModel('catalog/product');
        if ($productId = $this->_getProductIdBySku($productData['sku'])) {
            $product->load($productId);
        }
        
        $validAttributes = $this->_getProductAttributes($product);
        foreach ($productData as $_key => $_value) {
            if (array_key_exists($_key, $validAttributes) 
                    && ($product->getData($_key) != $_value)) {
                $this->_setProductHasChanges(true);
            }
            $product->setData($_key, $_value);
        }
        
        if (!$product->getAttributeSetId()) {
            $product->setAttributeSetId($product->getDefaultAttributeSetId());
        }
        
        if (array_key_exists('images', $productData)) {
            $this->addImages($product, $productData['images']);
        }
        
        if (array_key_exists('configurable_attributes', $productData)) {
            $this->setConfigurableAttributes($product, $productData['configurable_attributes']);
        }
        
        $this->_validateRequiredAttributes($product);
        
        if (!$product->getId() || $this->_getProductHasChanges()) {
            $product->save();
        }
        
        if (array_key_exists('parent_sku', $productData)) {
            $this->setParentProduct($product->getId(), $productData['parent_sku']);
        }
        
        return $product;
    }
    
    /**
     * Replace any product images with the filenames supplied in the images array.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array|string $images Array of image filenames or single filename
     * @return Ampersand_Api_Model_Catalog_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function addImages(Mage_Catalog_Model_Product $product, $images)
    {
        if (empty($images)) {
            return $this;
        }
        
        if (!is_array($images)) {
            $images = array($images);
        }
        
        $imagesToAdd = array();
        foreach ($images as $_image) {
            $_imageFile = $this->_getMediaImportDir() . DS . $_image;
            if (file_exists($_imageFile)) {
                $imagesToAdd[] = $_imageFile;
            }
        }
        
        if (count($imagesToAdd) > 0) {
            $setAttributes = $product->getTypeInstance()->getSetAttributes();
            if (array_key_exists('media_gallery', $setAttributes)) {
                $gallery = $setAttributes['media_gallery'];
                $galleryData = $product->getMediaGallery();
                foreach ($galleryData['images'] as $_imageToDelete) {
                    if ($gallery->getBackend()->getImage($product, $_imageToDelete['file'])) {
                        $gallery->getBackend()->removeImage($product, $_imageToDelete['file']);
                    }
                }
            }
            
            foreach ($imagesToAdd as $_imageFile) {
                $product->addImageToMediaGallery($_imageFile, null, true, false);
            }
            
            $galleryData = $product->getMediaGallery();
            foreach ($galleryData['images'] as $_newImage) {
                if ($_newImage['removed'] == 1 || $_newImage['disabled'] == 1) {
                    continue;
                }
                
                $product->setImage($_newImage['file']);
                $product->setSmallImage($_newImage['file']);
                $product->setThumbnail($_newImage['file']);
                
                break;
            }
            
            $this->_setProductHasChanges(true);
        }
        
        return $this;
    }
    
    /**
     * Define which attributes should be configurable for a configurable product.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array|string $attributes OPTIONAL Array of attribute codes or a single attribute code
     * as a string
     * @return Ampersand_Api_Model_Catalog_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setConfigurableAttributes(Mage_Catalog_Model_Product $product,
        $attributes = array()
    ) {
        // non configurable products cannot have configurable attributes
        if (!$product->isConfigurable()) {
            return;
        }
        
        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }
        
        // Get the configurable products already assigned to this product
        $existingAttributeIds = array();
        $existingAttributeCollection = $product->getTypeInstance()
            ->getConfigurableAttributeCollection();
        foreach ($existingAttributeCollection as $_existingAttribute) {
            $_attributeCode = $_existingAttribute->getProductAttribute()->getAttributeCode();
            if (!in_array($_attributeCode, $attributes)) {
                $_existingAttribute->delete();
            } else {
                $existingAttributeIds[$_existingAttribute->getAttributeId()] = $_existingAttribute;
            }
        }
        
        // Set any required attributes that dont already exist
        $newAttributes = array();
        foreach ($attributes as $_position => $_attributeCode) {
            $_attribute = Mage::getResourceModel('catalog/product')->getAttribute($_attributeCode);
            if (!$_attribute || !$_attribute->getId() || !$_attribute->getIsConfigurable()) {
                Mage::throwException("Configurable attribute `{$_attributeCode}` does not exist");
            }
            
            if (array_key_exists($_attribute->getId(), $existingAttributeIds)) {
                $_existingAttribute = $existingAttributeIds[$_attribute->getId()];
                if ($_existingAttribute->getPosition() != $_position) {
                    $_existingAttribute->setPosition($_position)->save();
                }
            } else {
                $newAttributes[] = array(
                    'id' => null,
                    'label' => $_attribute->getFrontendLabel(),
                    'use_default' => '1',
                    'position' => $_position,
                    'values' => array(),
                    'attribute_id' => $_attribute->getAttributeId(),
                    'attribute_code' => $_attribute->getAttributeCode(),
                    'frontend_label' => $_attribute->getFrontendLabel(),
                    'store_label' => $_attribute->getFrontendLabel(),
                    'html_id' => "configurable__attribute_{$_position}",
                );
            }
        }
        
        if (count($newAttributes) > 0) {
            $product->setConfigurableAttributesData($newAttributes);
            $this->_setProductHasChanges(true);
        }
        
        return $this;
    }
    
    /**
     * Assign a simple product to its parent.
     *
     * @param int $productId
     * @param string $parentSku
     * @return Ampersand_Api_Model_Catalog_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setParentProduct($productId, $parentSku)
    {
        if (!$parentId = $this->_getProductIdBySku($parentSku)) {
            Mage::throwException("Parent product with SKU `{$parentSku}` does not exist");
        }
        
        // Link so simple products appear as configurable product associated products
        $this->_addRelationship('catalog/product_super_link', array(
            'parent_id'     => $parentId,
            'product_id'    => $productId,
        ));

        // Link so indexing knows these two products are related
        $this->_addRelationship('catalog/product_relation', array(
            'parent_id' => $parentId,
            'child_id'  => $productId,
        ));
        
        return $this;
    }
    
    /**
     * Ensure all required attributes have valid values for saving.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Ampersand_Api_Model_Catalog_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateRequiredAttributes(Mage_Catalog_Model_Product $product)
    {
        foreach ($this->_requiredAttributes as $_attribute) {
            $_value = $product->getData($_attribute);
            if (is_null($_value) || $_value == '') {
                Mage::throwException("Missing `{$_attribute}`");
            }
        }
        
        return $this;
    }
    
    /**
     * Update product relationship fields in the database.
     *
     * @param string $tableAlias
     * @param array $data
     * @return Ampersand_Api_Model_Catalog_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _addRelationship($tableAlias, array $data)
    {
        if (!$data) {
            return $this;
        }
        
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $select = $connection->select();
        
        $select->from($resource->getTableName($tableAlias), key($data));
        foreach ($data as $_field => $_value) {
            $select->where("{$_field}=?", $_value);
        }
        
        if (!count($connection->fetchCol($select))) {
            $connection->insertMultiple($resource->getTableName($tableAlias), array($data));
        }
        
        return $this;
    }
    
    /**
     * Proxy method for retrieving a product ID by sku.
     *
     * @param string $sku
     * @return int 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getProductIdBySku($sku)
    {
        return Mage::getResourceModel('catalog/product')->getIdBySku($sku);
    }
    
    /**
     * Location of product image files.
     *
     * @return string
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getMediaImportDir()
    {
        return $this->_getMediaImportDir();
    }
    
    /**
     * Location of product image files.
     *
     * @return string
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getMediaImportDir()
    {
        return Mage::getBaseDir('media') . DS . 'import' . DS;
    }
    
    /**
     * Retrieve the flag to determine whether a product needs to be saved.
     *
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getProductHasChanges()
    {
        return $this->_productHasChanges;
    }
    
    /**
     * Update the flag to determine whether a product needs to be saved.
     *
     * @param bool $value
     * @return Ampersand_Api_Model_Catalog_Product 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _setProductHasChanges($value)
    {
        $this->_productHasChanges = $value;
        
        return $this;
    }
    
    /**
     * Retrieve valid product attributes.
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getProductAttributes(Mage_Catalog_Model_Product $product)
    {
        $attributeSetId = $product->getAttributeSetId();
        
        if (array_key_exists($attributeSetId, $this->_productAttributes)) {
            return $this->_productAttributes[$attributeSetId];
        }
        
        $entityType = Mage::getModel('eav/entity_type')->loadByCode('catalog_product');
        
        $attributeCollection = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityType)
            ->setAttributeSetFilter($product->getAttributeSetId());
        
        $attributes = array();
        foreach ($attributeCollection as $_attribute) {
            $attributes[$_attribute->getAttributeCode()] = $_attribute;
        }
        
        $this->_productAttributes[$attributeSetId] = $attributes;
        
        return $attributes;
    }
    
    /**
     * @param string $productSku
     * @param string $storeCode
     * @return array
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    public function info($productSku, $storeCode)
    {
        $this->_setStore($storeCode);
        $product = Mage::getModel('catalog/product');
        $product->load($product->getIdBySkU($productSku));
        if (!$product->getId()) {
            return;
        }
    
        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                        || !$product->isVisibleInSiteVisibility()
                        || !$product->isSaleable()
        ) {
            return;
        }
    
        return $this->_prepareProductArray($product);
    }
    
    /**
     * @param string $storeCode
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _setStore($storeCode)
    {
        $stores = Mage::app()->getStores(false, true);
    
        if (!array_key_exists($storeCode, $stores)) {
            return;
        }
    
        Mage::app()->setCurrentStore($storeCode);
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array 
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _prepareProductArray(Mage_Catalog_Model_Product $product)
    {
        $productData = array();
    
        $productData['id']              = $product->getId();
        $productData['name']            = $product->getName();
        $productData['sku']             = $product->getSku();
        $productData['price']           = $product->getPrice();
        $productData['type']            = $product->getTypeId();
        $productData['default_qty']     = $this->_getProductDefaultQty($product);
        $productData['url']             = $product->getProductUrl();
        $productData['image']           = $product->getImage();
        $productData['add_to_cart_url'] = $this->_getAddToCartUrl($product);
        $productData['query_string_add_to_cart_url'] = $this->_getQueryStringAddToCartUrl($product);
        $productData['redirect_to_cart'] = $this->_isAddingAProductRedirectsToCart();
        $productData['attributes']      = $this->_getProductOptions($product);
    
        return $productData;
    }
    
    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param null|Mage_Catalog_Model_Product
     *
     * @return int|float
     */
    protected function _getProductDefaultQty($product)
    {
        $qty = $this->_getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }
    
        return $qty;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return Ambigous <number, NULL>|NULL
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _getMinimalQty($product)
    {
        if (($stockItem = $product->getStockItem())) {
            return ($stockItem->getMinSaleQty() && $stockItem->getMinSaleQty() > 0
                            ? $stockItem->getMinSaleQty() * 1
                            : null);
        }
    
        return null;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _getConfigurableAttributes($product)
    {
        return $product->getTypeInstance(true)
                            ->getConfigurableAttributes($product);
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array 
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _getAllowProducts($product)
    {
        $products = array();
        $allProducts = $product->getTypeInstance(true)
                                    ->getUsedProducts(null, $product);
        foreach ($allProducts as $allowedProduct) {
            if ($allowedProduct->isSaleable()) {
                $products[] = $allowedProduct;
            }
        }
    
        return $products;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $_product
     * @return array
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _getProductOptions($_product)
    {
        if (!$_product->isConfigurable()) {
            return array();
        }
    
        $options = array();
        $info = array();
        $currentProduct = $_product;
    
        foreach ($this->_getAllowProducts($currentProduct) as $product) {
            $productId  = $product->getId();
            foreach ($this->_getConfigurableAttributes($currentProduct) as $attribute) {
                $productAttribute   = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue     = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }
    
                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }
    
        foreach ($this->_getConfigurableAttributes($currentProduct) as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info[$attributeId] = array(
                            'code' => $productAttribute->getAttributeCode(),
                            'label' => $attribute->getLabel(),
                            'options' => array()
            );
    
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if(!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
    
                    $info[$attributeId]['options'][] = array(
                                    'id' => $value['value_index'],
                                    'label' => $value['label'],
                    );
                }
            }
        }
    
        return $info;
    }
    
    /**
     * Validating of super product option value
     *
     * @param array $attributeId
     * @param array $value
     * @param array $options
     * @return boolean
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _validateAttributeValue($attributeId, &$value, &$options)
    {
        if(isset($options[$attributeId][$value['value_index']])) {
            return true;
        }
    
        return false;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _getAddToCartUrl($product)
    {
        return Mage::helper('checkout/cart')->getAddUrl($product, array());
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return string
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _getQueryStringAddToCartUrl($product)
    {
        return Mage::getUrl('checkout/cart/add', array('product' => $product->getId()));
    }
    
    /**
     * Config 'checkout/cart/redirect_to_cart
     *
     * @return boolean
     * @author Aditya Godara (aditya.godara@ampersandcommerce.com)
     */
    protected function _isAddingAProductRedirectsToCart()
    {
        return (boolean) Mage::getStoreConfig('checkout/cart/redirect_to_cart');
    }
}