<?php
class Ampersand_Sales_Model_OrderHelper extends Ampersand_Sales_Model_AbstractHelper
{
    /**
     * The order associated with this object.
     *
     * @var Mage_Sales_Model_Order $_order
     */
    protected $_order;
    
    /**
     * Order item id to order item mapping for this order.
     *
     * @var array $_orderItemIdOrderItemMap
     */
    protected $_orderItemIdOrderItemMap;
    
    /**
     * Initialize object with order if provided.
     *
     * @param Mage_Sales_Model_Order $order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function __construct(Mage_Sales_Model_Order $order = null)
    {
        parent::__construct();
        
        $this->setOrder($order);
    }
    
    /**
     * Associate order with this object and reset associated data.
     *
     * @param Mage_Sales_Model_Order $order
     * @return Ampersand_Sales_Model_OrderHelper 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        $this->_productIdSkuMap = null;
        
        return $this;
    }
    
    /**
     * Retrieve the order associated with this object.
     *
     * @return Mage_Sales_Model_Order 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getOrder()
    {
        return $this->_order;
    }
    
    /**
     * Retrieve an order item by sku and optionally child sku
     *
     * @param string $sku
     * @param string $childSku
     * @param bool $returnChild
     * @return Mage_Sales_Model_Order_Item 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getItemBySku($sku, $childSku = null, $returnChild = false)
    {
        $orderItem = null;
        $order = $this->getOrder();
        
        // initialize product id to sku mapping
        $productIdSkuMap = $this->getProductIdSkuMap($order->getAllItems());
        
        // attempt to match the order item
        foreach ($order->getAllItems() as $_item) {
            if (is_null($childSku)) {
                if ($sku != $productIdSkuMap[$_item->getProductId()]) {
                    continue;
                }
                if ($_item->getHasChildren() || $_item->getParentItemId()) {
                    continue;
                }
                $orderItem = $_item;
                break;
            } else {
                if ($childSku != $productIdSkuMap[$_item->getProductId()]) {
                    continue;
                }
                if (!$_item->getParentItemId()) {
                    continue;
                }
                $_parentItem = Mage::getModel('sales/order_item')->load($_item->getParentItemId());
                if ($sku != $productIdSkuMap[$_parentItem->getProductId()]) {
                    continue;
                }
                $orderItem = $returnChild ? $_item : $_parentItem;
            }
        }
        
        return $orderItem;
    }
}