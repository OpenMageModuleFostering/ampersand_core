<?php
class Ampersand_Api_Model_Sales_Quote extends Mage_Sales_Model_Order_Api
{
    /**
     * Create a quote.
     *
     * @param array $quoteData
     * $quoteData = array(
     *     'store_id' => '1',
     *     'customer_id' => '5',
     *     'shipping_method' => 'flatrate_flatrate',
     *     ... etc ...
     * );
     * 
     * @param array $productDatas
     * $productDatas = array(
     *     array(
     *         // either product id or sku (required)
     *         'product_id' => '123',
     *         'sku' => 'some_sku',
     *         
     *         // either child product id or child sku (optional)
     *         'child_product_id' => '123',
     *         'child_sku' => 'some_simple_sku',
     *         
     *         // configurable attribute values if no child sku or product id provided (optional)
     *         'configurable_attributes' => array(
     *             'attribute_code' => 'attribute_label',
     *         ),
     *         
     *         // quantity to add (optional)
     *         'qty' => '5',
     *         ... etc ...
     *     ),
     * );
     * 
     * @param array $addressDatas
     * $addressDatas = array(
     *     'billing' => array(
     *         'firstname' => 'John',
     *         'lastname' => 'Smith',
     *         ... etc ...
     *     ), 
     *     'shipping' => array(
     *         'firstname' => 'John',
     *         'lastname' => 'Smith',
     *         ... etc ...
     *     ), 
     * );
     * 
     * @return int 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function create(array $quoteData, array $productDatas, array $addressDatas)
    {
        try {
            // initialize quote data object
            $quoteDataObject = new Varien_Object();
            $quoteDataObject->addData($quoteData);

            // create a quote instance and add provided data
            $quote = Mage::getModel('sales/quote')->addData($quoteDataObject->getData());
            
            // set the quote to inactive so it does not interfere with the customers frontend quotes
            $quote->setIsActive('0');
            if (array_key_exists('is_active', $quoteData)) {
                $quote->setIsActive($quoteData['is_active']);
            }
            
            // assign customer to quote, if provided
            $customer = Mage::getModel('customer/customer')->load($quoteDataObject->getCustomerId());
            if ($customer->getId()) {
                $quote->setCustomer($customer);
            }

            // add products to quote
            $productSingleton = Mage::getSingleton('catalog/product');
            foreach ($productDatas as $_productData) {
                // initialize product data object
                $_productDataObject = new Ampersand_Object($_productData);

                // retrieve the product
                $_productId = $_productDataObject->getProductId();
                if (!$_productId) {
                    $_sku = $_productDataObject->getSku();
                    $_productId = $productSingleton->getIdBySku($_sku);
                }
                $_product = Mage::getModel('catalog/product')->load($_productId);
                $_sku = $_product->getSku();
                if (!$_sku || !$_product->getId()) {
                    $this->_fault(
                        'product_not_exists',
                        Mage::helper('ampersand_api')
                            ->__("Product with SKU '$_sku' could not be found.")
                    );
                }
                
                // retrieve the child product id if provided
                $_childProductId = $_productDataObject->getChildProductId();
                if (!$_childProductId) {
                    $_childSku = $_productDataObject->getChildSku();
                    $_childProductId = $productSingleton->getIdBySku($_childSku);
                }
                if (!$_childProductId) {
                    $_childProductId = null;
                }
                
                // add the product to the quote
                try {
                    $_request = Mage::helper('ampersand_sales/quote')->prepareProductRequest(
                        $_product,
                        $_productData,
                        $_childProductId,
                        $_productDataObject->getConfigurableAttributes()
                    );
                    $_item = $quote->addProduct($_product, $_request);
                    if (is_string($_item)) {
                        throw new Exception($_item);
                    }
                } catch (Exception $e) {
                    $this->_fault(
                        'order_not_created',
                        Mage::helper('ampersand_api')->__(
                            "Product with SKU '$_sku' exists but could not be added to order. "
                            . PHP_EOL . $e->getMessage()
                        )
                    );
                }
            }

            // ensure we have at least one item in the quote
            if (count($quote->getAllItems()) < 1) {
                $this->_fault('no_items_added');
            }
            
            // initialize address data types
            if (!array_key_exists('billing', $addressDatas)) {
                $addressDatas['billing'] = array();
            }
            if (!array_key_exists('shipping', $addressDatas)) {
                $addressDatas['shipping'] = $addressDatas['billing'];
            }
            $addressDatas['shipping']['shipping_method'] = $quoteDataObject->getShippingMethod();
            
            // add the billing address data to the quote
            $quote->getBillingAddress()
                ->addData($addressDatas['billing']);

            // add the shipping address data to the quote
            $quote->getShippingAddress()
                ->addData($addressDatas['shipping'])
                ->setCollectShippingRates(true);
            
            // set the payment information if provided
            $paymentData = $quoteDataObject->getPayment();
            if (!empty($paymentData)) {
                $quote->getPayment()->importData($paymentData);
            }
            
            // collect totals and save quote instance to database
            $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
        } catch (Mage_Api_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $this->_fault('quote_not_created', $e->getMessage());
        }
        
        return $quote->getId();
    }
    
    /**
     * Create a quote based on an order.
     *
     * @param int $orderId
     * @return int 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function createFromOrder($orderId)
    {
        // retrieve the order
        $order = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            $this->_fault('order_not_exists');
        }
        
        // prepare quote data
        $quoteData = array(
            'store_id' => $order->getStoreId(),
            'customer_id' => $order->getCustomerId(),
            'shipping_method' => $order->getShippingMethod(),
        );
        
        // prepare product data
        $orderItemIdMap = array();
        foreach ($order->getAllItems() as $_item) {
            $orderItemIdMap[$_item->getId()] = $_item;
        }
        $itemDatas = array();
        foreach ($order->getAllItems() as $_item) {
            $_itemProductOptions = $this->_getOrderItemProductOptions($_item);
            if ($_item->getParentItemId()) {
                $itemDatas[] = array_merge($_itemProductOptions, array(
                    'child_product_id' => $_item->getProductId(),
                    'product_id' => $orderItemIdMap[$_item->getParentItemId()]->getProductId(),
                    'qty' => $_item->getQtyOrdered(),
                ));
            } elseif (!$_item->getHasChildren()) {
                $itemDatas[] = array_merge($_itemProductOptions, array(
                    'product_id' => $_item->getProductId(),
                    'qty' => $_item->getQtyOrdered(),
                ));
            }
        }
        
        // prepare address data
        $addressAttributes = array(
            'customer_id',
            'customer_address_id',
            'company',
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'street',
            'city',
            'country_id',
            'region',
            'region_id',
            'postcode',
            'email',
            'telephone',
            'fax',
        );
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        foreach ($addressAttributes as $_attribute) {
            $addressDatas['billing'][$_attribute] = $billingAddress->getData($_attribute);
            $addressDatas['shipping'][$_attribute] = $shippingAddress->getData($_attribute);
        }
        
        // create the order and return the order id
        return $this->create($quoteData, $itemDatas, $addressDatas);
    }
    
    /**
     * Retrieve any additional order item data required for certain product types.
     * 
     * @param Mage_Sales_Model_Order_Item $_item
     * @return array
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getOrderItemProductOptions($_item)
    {
        $options = array();
        
        switch ($_item->getProductType()) {
            case Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE:
                $itemProductOptions = $_item->getProductOptions();
                $options['links'] = array_key_exists('links', $itemProductOptions) 
                    ? $itemProductOptions['links'] : array();
                break;
            
            default:
                break;
        }
        
        return $options;
    }
    
    /**
     * Convert a quote to an order.
     *
     * @param int $quoteId
     * @param array $orderData
     * @param array $paymentData
     * @return int 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function convertToOrder($quoteId, $orderData = array(), $paymentData = array())
    {
        try {
            // retrieve the quote
            $quote = Mage::getModel('sales/quote');
            if (method_exists($quote, 'loadByIdWithoutStore')) {
                $quote->loadByIdWithoutStore($quoteId);
            } else {
                $quote->load($quoteId);
            }
            
            if (!$quote->getId()) {
                $this->_fault('quote_not_exists');
            }
            
            // set the payment information if provided
            if (!empty($paymentData)) {
                $quote->getPayment()->importData($paymentData);
            }
            
            // collect totals and save quote instance to database
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            
            // initialise service with order data, if provided
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->setOrderData($orderData);
            
            // convert to order
            $order = $service->submit();
        } catch (Mage_Api_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $this->_fault('order_not_created', $e->getMessage());
        }
        
        return $order->getId();
    }
}