<?php
/**
 * Ampersand Magento Suite
 *
 * @category    Ampersand_Magento
 * @package     Ampersand_Api
 * @subpackage  Model
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandcommerce.com)
 * @license     TBC
 */

/**
 * @category    Ampersand_Magento
 * @package     Ampersand_Api
 * @subpackage  Model
 * @author      Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
 */

/**
 * @todo 
 * - Currency handling and conversion
 * - If increment id exists (on second check) the order has already been created
 * - If invoice or shipment fails, the order has already been created
 * - Items can be shipped even if they have not been invoiced
 * - implement SOAP aspects of this module
 */

/**
 * @example
$orderData = array(
    'store_id' => 'default', // required
    'grand_total' => '520.00', // required
    'shipping_incl_tax' => '20.00', // required
    'tax_amount' => '100.00', // required
    'shipping_tax_amount' => '0', // required
    'order_currency_code' => 'GBP', // required
    'shipping_method' => 'flatrate_flatrate', // required
    'payment_method' => 'checkmo', // required
    'customer_email' => time() . '@ampersandcommerce.com', // optional
    'customer_firstname' => 'Joseph', // optional
    'customer_lastname' => 'McDermott', // optional
    'ext_order_id' => '123-12415-12312', // optional
    'ext_customer_id' => '876123', // optional
    'reserved_order_id' => '', // optional
    'created_at' => 1322134810, // optional
    'items' => array(
        array(
            'sku' => 'JRMTEST', // required
            'price_incl_tax' => '100.00', // required
            'tax_amount' => '20.00', // required
            'qty_ordered' => '5', // required
            'qty_invoiced' => '5', // optional
            'qty_shipped' => '0', // optional
            'ext_order_item_id' => 'asd6a987asd', // optional
        ),
        array(
            'sku' => 'Configurable_Test',
            'price_incl_tax' => '9.99',
            'tax_amount' => '0.00',
            'qty_ordered' => '1',
            'options' => array(
                'colour' => 'red',       // options must exist
                'size' => 'small',       // options must exist 
            ),
        ),
    ),
    'shipping_address' => array( // optional
        'firstname' => 'Joseph',
        'lastname' => 'McDermott',
        'street' => 'address line 1' . PHP_EOL . 'address line 2',
        'city' => 'Manchester',
        'region_id' => 'Lancs',
        'country_id' => 'UK',
        'postcode' => 'AB11AB',
        'telephone' => '0123456789',
        'fax' => '0123456789',
    ),
    'billing_address' => array( // optional
        'firstname' => 'Joseph',
        'lastname' => 'McDermott',
        'street' => 'address line 1' . PHP_EOL . 'address line 2',
        'city' => 'Manchester',
        'region_id' => 'Lancs',
        'country_id' => 'UK',
        'postcode' => 'AB11AB',
        'telephone' => '0123456789',
        'fax' => '0123456789',
    ),
);
Mage::getModel('ampersand_api/sales_order')->create($orderData, true, array(
 * 
 * ));
*/
class Ampersand_Api_Model_Sales_Order extends Mage_Sales_Model_Order_Api
{
    /**
     * Quote object for preparing order.
     * 
     * @var Mage_Sales_Model_Quote $_quote 
     */
    protected $_quote;
    
    /**
     * Order object once created.
     * 
     * @var Mage_Sales_Model_Order $_order 
     */
    protected $_order;
    
    /**
     * Active store to save orders in.
     * 
     * @var Mage_Core_Model_Store $_store
     */
    protected $_store;
    
    /**
     * Our supplied data in a nested Ampersand Object format.
     * 
     * @var Ampersand_Object $_dataObject
     */
    protected $_dataObject;
    
    /**
     * Flag for whether stores have been initialised.
     * 
     * @var bool $_storesInitialised
     */
    protected $_storesInitialised = false;
    
    /**
     * Additional data to be added to an order instance
     *
     * @var array
     */
    protected $_additionalData = array();
    
    /**
     * Create a Magento order from a nested array of data, and return the
     * increment id of the newly created order.
     * 
     * @param array $orderData
     * @param bool $processRelatedObjects
     * @param array $additionalData
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function create($orderData, $processRelatedObjects = false, $additionalData = array())
    {
        try {
            $this->prepareDataObject($orderData);            
            $this->_additionalData = $additionalData;
            
            $this->_validateDataObject();
            $this->_checkIncrementId(false);
            $this->_initQuote();
            $this->_assignCustomer();
            $this->_assignProducts();
            $this->_assignShippingMethod();
            $this->_assignPaymentMethod();
            $this->_convertQuoteToOrder();
            
            if ($processRelatedObjects) {
                $this->_invoiceOrder();
                $this->_dispatchOrder();
                $this->_refundOrder(); 
            }
        } catch (Mage_Api_Exception $e) {
            $this->_fault($e->getMessage(), $e->getCustomMessage());
        } catch (Exception $e) {
            $this->_fault('order_not_created', $e->getMessage());
        }
        
        return $this->_order->getIncrementId();
    }
    
    /**
     * @todo Not actually fully working yet...
     */
    public function createFromOrder($order, $paymentData = array(), $productDatas = array())
    {
        // load the order create model
        $orderCreate = Mage::getModel('adminhtml/sales_order_create');
        $orderCreate->getSession()->getOrder()->reset();
        
        // use the same shipping method as the provided order
        $orderCreate->getSession()->setUseOldShippingMethod(true);
        
        // initialize a new order based on the one provided
        Mage::unregister('rule_data');
        $orderCreate->initFromOrder($order);
        
        // modify the payment data to be used for this transaction
        $orderCreate->getQuote()->getPayment()->unsMethodInstance();
        if (!empty($paymentData)) {
            $orderCreate->setPaymentData($paymentData);
        }
        
        // modify the products to be included in the order
        if (count($productDatas)) {
            $qtyUpdateData = array();
            foreach ($orderCreate->getQuote()->getItemsCollection() as $_item) {
                $newQty = in_array($_item->getProductId(), $productDatas)
                    ? $productDatas[$_item->getProductId()]
                    : 0;
                $_item
                    ->setQty($newQty)
                    ->setQtyToAdd($newQty);
            }
        }
        
        // we dont want the new order linked to the old one
        $orderCreate->getSession()->getOrder()->setId(null);
        
        // recollect the cart
        $orderCreate->getCustomerCart()
            ->collectTotals()
            ->save();
        $orderCreate->setRecollect(true);
        
        // give an opportunity for any changes before creating the order
        Mage::dispatchEvent(
            'ampersand_api_sales_order_createfromorder_before',
            array(
                'order' => $order,
                'order_create_model' => $orderCreate,
            )
        );
        
        // create the order
        return $orderCreate->createOrder();
    }
    
    /**
     * Convert the provided nested array to a nested Ampersand Object
     * and perform any bootstrapping and validation as required.
     * 
     * @param array $orderData
     * @return Ampersand_Object
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function prepareDataObject($orderData)
    {
        $this->_dataObject = Ampersand_Object::arrayToObject($orderData);
        
        $this->_prepareCustomer();
        $this->_prepareProducts();
        
        return $this->_dataObject;
    }
    
    /**
     * Populate shipping with billing details and vicaversa if missing.
     * Also set guest checkout if no customer email or address information.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _prepareCustomer()
    {
        if (!$this->_dataObject->getShippingAddress()) {
            $this->_dataObject->setShippingAddress(new Ampersand_Object(array()));
        }
        
        if (!$this->_dataObject->getBillingAddress()) {
            $this->_dataObject->setBillingAddress(new Ampersand_Object(array()));
        }
        
        if (!$this->_dataObject->getCustomerEmail()) {
            $this->_dataObject->setCustomerIsGuest(true);
            return $this;
        }
        
        $shippingAddressData = $this->_dataObject->getShippingAddress()->getData();
        $billingAddressData = $this->_dataObject->getBillingAddress()->getData();
        
        $primaryAddressData = array();
        if ($shippingAddressData) {
            $primaryAddressData = $shippingAddressData;
        } elseif ($billingAddressData) {
            $primaryAddressData = $billingAddressData;
        } else {
            $this->_dataObject->setCustomerIsGuest(true);
            return $this;
        }
        
        if (!$shippingAddressData) {
            $shippingAddressData = $primaryAddressData;
        }
        
        if (!$billingAddressData) {
            $billingAddressData = $primaryAddressData;
        }
        
        $this->_dataObject->getShippingAddress()->setData($shippingAddressData);
        $this->_dataObject->getBillingAddress()->setData($billingAddressData);
        
        $customerFieldsToPopulate = array(
            'customer_firstname' => 'firstname',
            'customer_lastname' => 'lastname',
        );
        $primaryAddressObject = new Ampersand_Object($primaryAddressData);
        foreach ($customerFieldsToPopulate as $_customerField => $_addressField) {
            if (!$this->_dataObject->getData($_customerField)) {
                $this->_dataObject->setData(
                    $_customerField, $primaryAddressObject->getData($_addressField));
            }
        }
        
        return $this;
    }
    
    /**
     * Create sku to itemData mapping and auto-populate product fields.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _prepareProducts()
    {
        $skuToItemData = array();
        foreach ($this->_dataObject->getItems()->getData() as $_skuItem) {
            $skuToItemData[$_skuItem->getSku()] = $_skuItem;
        }
        $this->_dataObject->setSkuToItemData($skuToItemData);
        
        return $this;
    }
    
    /**
     * Ensure our data object contains all the information we require.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateDataObject()
    {
        $this->_validateStore();
        $this->_validateAddresses();
        $this->_validateProducts();
        $this->_validateOrderData();
        $this->_validateShippingMethod();
        $this->_validatePaymentMethod();
        
        return $this;
    }
    
    /**
     * Ensure a valid store has been selected.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateStore()
    {
        $this->_setStore($this->_dataObject->getStoreId());
        $this->_getStore();
        
        return $this;
    }
    
    /**
     * Ensure valid address data has been provided.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateAddresses()
    {
        if (!$this->_dataObject->getCustomerIsGuest()) {
            $this->_validateAddress($this->_dataObject->getShippingAddress()->getData());
            $this->_validateAddress($this->_dataObject->getBillingAddress()->getData());
        }
        
        return $this;
    }
    
    /**
     * Ensure all required address fields exist in the provided address data.
     *
     * @param array $addressData
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateAddress($addressData)
    {
        $requiredFields = array(
            'firstname',
            'lastname',
            'street',
            'city',
            'region_id',
            'country_id',
            'postcode',
            'telephone',
            'fax',
        );
        
        foreach ($requiredFields as $_field) {
            if (!array_key_exists($_field, $addressData)) {
                $this->_fault('invalid_data', 
                    'Address information missing.' . PHP_EOL
                    . 'Required: ' . implode(' / ', $requiredFields) . PHP_EOL
                    . 'Provided: ' . implode(' / ', array_keys($addressData)));
            }
        }
                
        return $this;
    }
    
    /**
     * Ensure valid product data has been provided.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateProducts()
    {
        foreach ($this->_dataObject->getSkuToItemData() as $_sku => $_item) {
            $productId = Mage::getSingleton('catalog/product')->getIdBySku($_item->getSku());
            if (!$productId) {
                $this->_fault('product_not_exists', "Product with Sku {$_item->getSku()} does not exist");
            }
            
            $requiredFields = array(
                'sku',
                'price_incl_tax',
                'tax_amount',
                'qty_ordered',
            );
            
            foreach ($requiredFields as $_field) {
                if (is_null($_item->getData($_field))) {
                    $this->_fault('product_not_added', "Insufficient data for {$_item->getSku()}");
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Ensure valid order data has been provided.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateOrderData()
    {
        $requiredFields = array(
            'store_id',
            'grand_total',
            'shipping_incl_tax',
            'tax_amount',
            'shipping_tax_amount',
            'order_currency_code',
            'shipping_method',
            'payment_method',
        );
        
        foreach ($requiredFields as $_field) {
            if (is_null($this->_dataObject->getData($_field))) {
                $this->_fault('order_not_created', 'Insufficient data for order');
            }
        }
        
        $currencyCode = $this->_dataObject->getOrderCurrencyCode();
        if (!in_array($currencyCode, $this->_getStore()->getAvailableCurrencyCodes())) {
            $this->_fault('currency_code_invalid', "Currency code {$currencyCode} is invalid");
        }
        
        return $this;
    }
    
    /**
     * Ensure a valid shipping method has been provided.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validateShippingMethod()
    {
        $shippingMethod = $this->_dataObject->getShippingMethod();
        $shippingMethodParts = explode('_', $shippingMethod);
        if (count($shippingMethodParts) != 2) {
            $this->_fault('shipping_code_not_exists', "Shipping code {$shippingMethod} does not exist");
        }
        
        $carrierCode = $shippingMethodParts[0];
        $method = $shippingMethodParts[1];
        $carrier = Mage::getModel('shipping/config')
            ->getCarrierInstance($carrierCode, $this->_getStore());
        
        if (!$carrier || !array_key_exists($method, $carrier->getAllowedMethods())) {
            $this->_fault('shipping_code_not_exists', "Shipping code {$shippingMethod} does not exist");
        }
        
        return $this;
    }
    
    /**
     * Ensure a valid payment method has been provided.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _validatePaymentMethod()
    {
        $method = $this->_dataObject->getPaymentMethod();
        $methods = Mage::getModel('payment/config')
            ->getActiveMethods($this->_getStore());
        
        if (!array_key_exists($method, $methods)) {
            $this->_fault('payment_method_not_exists', "Payment method {$method} does not exist");
        }
        
        return $this;
    }
    
    /**
     * Ensure the order increment id does not already exist.
     *
     * @param string $incrementId
     * @param bool $allowOne
     * @return Ampersand_Integration_Helper_Handler_Order  
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _checkIncrementId($checkAfterCreated = false)
    {   
        $incrementId = $checkAfterCreated 
            ? $this->_order->getIncrementId() 
            : $this->_dataObject->getReservedOrderId();
        
        if ($incrementId) {
            $size = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToFilter('increment_id', $incrementId)
                ->getSize();
            
            /**
             * @todo this will error as expected, however the order is already created...
             */
            if ($size > (int)$checkAfterCreated) {
                $this->_fault('duplicate_order_id', 
                    "The order was created but with a duplicate increment id {$incrementId}");
            }
        }
        
        return $this;
    }
    
    /**
     * Initialise quote object.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _initQuote()
    {
        $this->_quote = Mage::getModel('sales/quote');
        
        $reservedOrderId = $this->_dataObject->getReservedOrderId();
        $this->_quote->setReservedOrderId($reservedOrderId);
        
        return $this;
    }
    
    /**
     * If a customer already exists with the provided email address, we use 
     * that customer, otherwise a new customer must be created from scratch.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _assignCustomer()
    {
        if ($this->_dataObject->getCustomerIsGuest()) {
            $this->_quote->setCustomerIsGuest(true);
            return $this;
        }
        
        $customer = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter('email', $this->_dataObject->getCustomerEmail())
            ->getFirstItem();
        
        if (!$customer || (!$customerId = $customer->getId())) {
            $newCustomerId = $this->_createCustomer();
            $customer = Mage::getModel('customer/customer')->load($newCustomerId);
            if (!$customer->getId()) {
                $this->_fault('customer_not_exists', 'Unable to create a new customer');
            }
        }
        
        $this->_quote->setCustomer($customer);
        
        return $this;
    }
    
    /**
     * Create a new customer using the Magento Api.
     *
     * @return int
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _createCustomer()
    {
        if (!$this->_dataObject->getCustomerEmail()) {
            $this->_fault('customer_not_exists', 'Customer email address was not provided.');
        }
        
        $newCustomer = array(
            'firstname' => $this->_dataObject->getCustomerFirstname(),
            'lastname' => $this->_dataObject->getCustomerLastname(),
            'email' => $this->_dataObject->getCustomerEmail(),
            'website_id' => $this->_getWebsite()->getId(),
            'store_id' => $this->_getStore()->getId(),
        );
        $customerId = Mage::getModel('customer/customer_api')
            ->create($newCustomer);
        
        $this->_createCustomerAddress($customerId);
        
        return $customerId;
    }
    
    /**
     * Create a new address and assign to the customer id provided.
     *
     * @param int $customerId
     * @return int
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _createCustomerAddress($customerId)
    {
        $additionalFields = array(
            'is_default_shipping'   => '1',
            'is_default_billing'    => '1',
        );
        $newCustomerAddress = array_merge(
            $this->_dataObject->getShippingAddress()->getData(),
            $additionalFields);
        
        $addressId = Mage::getModel('ampersand_api/customer_address')
            ->create($customerId, $newCustomerAddress);
        
        return $addressId;
    }
   
     /** 
      * create records in Credit Memos 
      *  
      * @return Ampersand_Api_Model_Sales_Order  
      * @author Wenjiang Xu <wenjiang.xu@ampersandcommerce.com> 
      */ 
     protected function _refundOrder() 
     { 
         try { 
             $okToRefund = false; 
             $itemQtyData = $this->_getItemQtyData('refunded'); 
             foreach ($itemQtyData as $_itemQty) { 
                 if ($_itemQty > 0) { 
                     $okToRefund = true; 
                     break; 
                 } 
             } 
              
             if ($okToRefund) { 
                 $refund = Mage::getModel('sales/order_creditmemo_api'); 
                 $refund->create($this->_order->getIncrementId(),$itemQtyData); 
             } 
         } catch (Mage_Api_Exception $e) { 
             $this->_fault($e->getMessage(), $e->getCustomMessage()); 
         } catch (Exception $e) { 
             $this->_fault('creditmemo_not_created', $e->getMessage()); 
         } 
         return $this; 
     }
    
    /**
     * Add products to the quote which will later be converted to an order.
     * 
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _assignProducts()
    {
        foreach ($this->_dataObject->getItems()->getData() as $_item) {
            $productId = Mage::getSingleton('catalog/product')->getIdBySku($_item->getSku());
            $_product = Mage::getModel('catalog/product')->load($productId);
            if (!$_product->getId()) {
                $this->_fault('product_not_exists', 
                    "Product with sku '{$_item->getSku()}' could not be found");
            }
            $this->_quote->addProduct($_product, $this->_prepareRequest($_product, $_item));
        }
        
        if (!count($this->_quote->getAllItems())) {
            $this->_fault('no_items_added', 'No products were added to the order.');
        }
        
        return $this;
    }
    
    /**
     * Emulates request from user based on quantity and selected attributes
     * 
     * @author Matthew Haworth <matthew.haworth@ampersandcommerce.com>
     * @param Mage_Catalog_Model_Product $product
     * @param array $item
     * @return \Varien_Object 
     */
    protected function _prepareRequest($product, $item) {
        $request = new Varien_Object();
        $request->setQty($item->getQtyOrdered());
        if ($item->getOptions()) {
            $options = $item->getOptions()->getData();
            foreach (array_keys($options) as $_attributeKey) {
                $attribute = Mage::getModel('catalog/product')
                    ->getResource()
                    ->getAttribute($_attributeKey);

                if (!$attribute) {
                    $this->_fault('attribute_not_found', 'Could not find attribute from code');
                }

                // Replace value
                $newValue = $attribute->getSource()->getOptionId($options[$_attributeKey]);
                if (!$newValue) {
                    $this->_fault('attribute_value_not_found', 'Could not find attribute value from code');
                }
                
                $options[$_attributeKey] = $newValue;
                
                // Replace key
                $options[$attribute->getId()] = $options[$_attributeKey];
                unset($options[$_attributeKey]);
            }

            $childProduct = Mage::getSingleton('catalog/product_type_configurable')
                ->getProductByAttributes($options, $product);

            if (!$childProduct) {
                $this->_fault('child_product_not_found', 'Could not find child product from options');
            }
            
            $skuToItemData = $this->_dataObject->getSkuToItemData();
            $skuToItemData[$childProduct->getSku()] = $skuToItemData[$product->getSku()];
            unset($skuToItemData[$product->getSku()]);
            $this->_dataObject->setSkuToItemData($skuToItemData);

            $request->setSuperAttribute($options);
        }

        return $request;
    }
    
    /**
     * Assign shipping method. If a price provided or no method defined, use flat
     * rate and override the value to match the value we have received.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _assignShippingMethod()
    {
        $storeCode = $this->_getStore()->getCode();
        $config = Mage::getConfig()->getNode("stores/$storeCode/carriers/flatrate");
        $config->setNode('price', $this->_dataObject->getShippingInclTax())
            ->setNode('type', 'O')
            ->setNode('handling_fee', '0');
        
        $this->_quote->getShippingAddress()
            ->addData($this->_dataObject->getShippingAddress()->getData())
            ->setShippingMethod($this->_dataObject->getShippingMethod())
            ->setShippingDescription($this->_dataObject->getShippingMethod())
            ->setCollectShippingRates(true);
        
        return $this;
    }
    
    /**
     * Configure billing and payment information.
     * 
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _assignPaymentMethod()
    {
        $this->_quote->getBillingAddress()
            ->addData($this->_dataObject->getBillingAddress()->getData());
        
        $payment = $this->_quote->getPayment();
        if ($paymentDataObject = $this->_dataObject->getPayment()) {
            $paymentDataObject->getDataSetDefault(
                'method', $this->_dataObject->getPaymentMethod()
            );
            
            // remove this first so we dont create the 'additional_information' node as an object
            $additionalInformation = $paymentDataObject->getAdditionalInformation();
            $paymentDataObject->unsetData('additional_information');
            
            $payment->importData($paymentDataObject->getData());
            
            if ($additionalInformation instanceof Ampersand_Object) {
                foreach ($additionalInformation->getData() as $_key => $_value) {
                    $payment->setAdditionalInformation($_key, $_value);
                }
            }
        } else {
            $payment->setMethod($this->_dataObject->getPaymentMethod());
        }
        
        return $this;
    }
    
    /**
     * Convert quote to order.
     * 
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _convertQuoteToOrder()
    {
        $this->_quote->collectTotals()->save();
        $convertQuote = Mage::getModel('sales/convert_quote');
        $this->_order = $convertQuote->addressToOrder($this->_quote->getShippingAddress())
            ->setBillingAddress($convertQuote->addressToOrderAddress($this->_quote->getBillingAddress()))
            ->setShippingAddress($convertQuote->addressToOrderAddress($this->_quote->getShippingAddress()));
        // Mage_Sales_Model_Order->setPayment() returns payment instance, not order!
        $this->_assignPaymentMethod();
        $this->_order->setPayment($convertQuote->paymentToOrderPayment($this->_quote->getPayment()));

        $skuToItemData = $this->_dataObject->getSkuToItemData();
        foreach ($this->_quote->getAllItems() as $_item) {
            $_orderItem = $convertQuote->itemToOrderItem($_item);
            
            if ($_item->getParentItem()) {
                $_orderItem->setParentItem($this->_order->getItemByQuoteItemId($_item->getParentItem()->getId()));
            }
            
            $itemQty = $skuToItemData[$_item->getSku()]->getQtyOrdered();
            $itemTaxAmount = $skuToItemData[$_item->getSku()]->getTaxAmount();
            $itemPriceInclTax = $skuToItemData[$_item->getSku()]->getPriceInclTax();
            $itemPriceExclTax = $itemPriceInclTax - $itemTaxAmount;
                        
            $linePriceExclTax = $itemPriceExclTax * $itemQty;
            $linePriceInclTax = $itemPriceInclTax * $itemQty;

            $taxPercent = ($itemTaxAmount / $itemPriceInclTax) * 100;
            $lineTaxAmount = $itemTaxAmount * $itemQty;
            
            $_orderItem->addData(array(
                'price' => $itemPriceExclTax,
                'base_price' => $itemPriceExclTax,
                'converted_price' => $itemPriceExclTax,
                'calculation_price' => $itemPriceExclTax,
                'base_calculation_price' => $itemPriceExclTax,
                'original_price' => $itemPriceExclTax,
                'base_original_price' => $itemPriceExclTax,
                'price_incl_tax' => $itemPriceInclTax,
                'base_price_incl_tax' => $itemPriceInclTax,
                'row_total' => $linePriceExclTax,
                'base_row_total' => $linePriceExclTax,
                'taxable_amount' => $linePriceExclTax,
                'base_taxable_amount' => $linePriceExclTax,
                'row_total_incl_tax' => $linePriceInclTax,
                'base_row_total_incl_tax' => $linePriceInclTax,
                'tax_percent' => $taxPercent,
                'tax_amount' => $itemTaxAmount,
                'base_tax_amount' => $itemTaxAmount,
                'ext_order_item_id' => $skuToItemData[$_item->getSku()]->getExtOrderItemId(),
            ));
            $this->_order->addItem($_orderItem);
        }
        
        $subtotalInclTax = $this->_dataObject->getGrandTotal() - $this->_dataObject->getShippingInclTax();
        $subtotalTaxMultiplier = 1 - ($this->_dataObject->getTaxAmount() / $subtotalInclTax);
        $subtotalExclTax = $subtotalInclTax * $subtotalTaxMultiplier;
        
        $shippingInclTax = $this->_dataObject->getShippingInclTax();
        if ($shippingTaxAmount = $this->_dataObject->getShippingTaxAmount()) {
            $shippingTaxMultiplier = 1 - ($shippingTaxAmount / $shippingInclTax);
            $shippingExclTax = $shippingInclTax * $shippingTaxMultiplier;
        } else {
            $shippingExclTax = $shippingInclTax;
        }
        
        $this->_order->addData(array(
            'store_id' => $this->_getStore()->getId(),
            'quote_base_grand_total' => $this->_dataObject->getGrandTotal(),
            'subtotal' => $subtotalExclTax,
            'base_subtotal' => $subtotalExclTax,
            'subtotal_incl_tax' => $subtotalInclTax,
            'base_subtotal_incl_tax' => $subtotalInclTax,
            'shipping_amount' => $shippingExclTax,
            'base_shipping_amount' => $shippingExclTax,
            'shipping_incl_tax' => $shippingInclTax,
            'base_shipping_incl_tax' => $shippingInclTax,
            'shipping_tax_amount' => $shippingTaxAmount,
            'base_shipping_tax_amount' => $shippingTaxAmount,
            'tax_amount' => $this->_dataObject->getTaxAmount(),
            'base_tax_amount' => $this->_dataObject->getTaxAmount(),
            'grand_total' => $this->_dataObject->getGrandTotal(),
            'base_grand_total' => $this->_dataObject->getGrandTotal(),
            'ext_order_id' => $this->_dataObject->getExtOrderId(),
            'ext_customer_id' => $this->_dataObject->getExtCustomerId(),
            'order_currency_code' => $this->_dataObject->getOrderCurrencyCode(),
            'created_at' => $this->_dataObject->getCreatedAt(),
        ));
        
        $this->_order->addData($this->_additionalData);
        
        $observer = new Varien_Event_Observer();
        $observer->setEvent(new Varien_Event(array (
            'quote' => $this->_quote,
        )));
        
        Mage::getSingleton('cataloginventory/observer')->subtractQuoteInventory($observer);
        
        try {
            $this->_order->place()->save();

            Mage::getSingleton('cataloginventory/observer')->reindexQuoteInventory($observer);
        } catch (Exception $e) {
            Mage::getSingleton('cataloginventory/observer')->revertQuoteInventory($observer);
            
            throw $e;
        }
        
        $this->_checkIncrementId(true);
        
        return $this;
    }
    
    /**
     * Invoice the order using Magento Api.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _invoiceOrder()
    {
        try {
            $okToInvoice = false;
            $itemQtyData = $this->_getItemQtyData('invoiced');
            foreach ($itemQtyData as $_itemQty) {
                if ($_itemQty > 0) {
                    $okToInvoice = true;
                    break;
                }
            }
            
            if ($okToInvoice) {
                Mage::getModel('sales/order_invoice_api')
                    ->create($this->_order->getIncrementId(), $itemQtyData);
            }
        } catch (Mage_Api_Exception $e) {
            $this->_fault($e->getMessage(), $e->getCustomMessage());
        } catch (Exception $e) {
            $this->_fault('invoice_not_created', $e->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Dispatch the order using the Magento Api.
     *
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _dispatchOrder()
    {
        try {
            $okToDispatch = false;
            $itemQtyData = $this->_getItemQtyData('shipped');
            foreach ($itemQtyData as $_itemQty) {
                if ($_itemQty > 0) {
                    $okToDispatch = true;
                    break;
                }
            }
            
            if ($okToDispatch) {
                Mage::getModel('sales/order_shipment_api')
                    ->create($this->_order->getIncrementId(), $itemQtyData);
            }
        } catch (Mage_Api_Exception $e) {
            $this->_fault($e->getMessage(), $e->getCustomMessage());
        } catch (Exception $e) {
            $this->_fault('shipment_not_created', $e->getMessage());
        }
        
        return $this;
    }
    
    /**
     * Retrieve an array of orderItemId => quantity for shipping or invoicing.
     *
     * @param string $type
     * @return array
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getItemQtyData($type)
    {
        $skuToItemData = $this->_dataObject->getSkuToItemData();
        
        $qtyData = array();
        foreach ($this->_order->getAllItems() as $_orderItem) {
            $itemQty = $skuToItemData[$_orderItem->getSku()]->getData("qty_{$type}");
            $qtyOrdered = $skuToItemData[$_orderItem->getSku()]->getData('qty_ordered');
            if ($itemQty > $qtyOrdered) {
                $this->_fault("qty_{$type}_exceeds_qty_ordered");
            }
            
            if ($itemQty < 0) {
                $this->_fault("qty_{$type}_cannot_be_negative");
            }
            
            $qtyData[$_orderItem->getId()] = $itemQty;
        }
        
        return $qtyData;
    }
    
    /**
     * Specify which store to save orders in.
     * 
     * @param mixed $storeId
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _setStore($storeId)
    {
        if (!$storeId) {
            return $this;
        }
        
        $this->_reinitStores();
        
        try {
            $store = Mage::app()->getStore($storeId);
        } catch (Exception $e) {
            $this->_fault('no_store_selected', 'Magento store id invalid');
        }
        
        if ($store->getId()) {
            $this->_store = $store;
        }
        
        return $this;
    }
    
    /**
     * Retrieve the active store.
     *
     * @return Mage_Core_Model_Store 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getStore()
    {
        if (!$this->_store || !$this->_store->getId()) {
            $this->_fault('no_store_selected', 'Magento store not specified');
        }
        
        return $this->_store;
    }
    
    /**
     * Some scripts run before Magento has a change to initialise stores,
     * and so requests for specific stores or websites may not be available.
     * 
     * @return Ampersand_Api_Model_Sales_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _reinitStores()
    {
        if (!$this->_storesInitialised) {
            Mage::app()->reinitStores();
            $this->_storesInitialised = true;
        }
        
        return $this;
    }
    
    /**
     * Retrieve the current website from the active store.
     * 
     * @return Mage_Core_Model_Website
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getWebsite()
    {
        return $this->_getStore()->getWebsite();
    }
}