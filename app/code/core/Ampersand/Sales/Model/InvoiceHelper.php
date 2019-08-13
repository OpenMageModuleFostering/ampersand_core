<?php
class Ampersand_Sales_Model_InvoiceHelper extends Ampersand_Sales_Model_AbstractHelper
{
    /**
     * The invoice associated with this object.
     *
     * @var Mage_Sales_Model_Order_Invoice $_invoice
     */
    protected $_invoice;
    
    /**
     * Order item id to invoice item mapping for this invoice.
     *
     * @var array $_orderItemIdInvoiceItemMap
     */
    protected $_orderItemIdInvoiceItemMap;
    
    /**
     * Initialize object with invoice if provided.
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function __construct(Mage_Sales_Model_Order_Invoice $invoice = null)
    {
        parent::__construct();
        
        $this->setInvoice($invoice);
    }
    
    /**
     * Associate invoice with this object and reset associated data.
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Ampersand_Sales_Model_InvoiceHelper 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $this->_invoice = $invoice;
        $this->_productIdSkuMap = null;
        $this->_orderItemIdInvoiceItemMap = null;
        
        return $this;
    }
    
    /**
     * Retrieve the invoice associated with this object.
     *
     * @return Mage_Sales_Model_Order_Invoice 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getInvoice()
    {
        return $this->_invoice;
    }
    
    /**
     * Retrieve an invoice item by sku and optionally child sku
     *
     * @param string $sku
     * @param string $childSku
     * @param bool $returnChild
     * @return Mage_Sales_Model_Order_Invoice_Item 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getItemBySku($sku, $childSku = null, $returnChild = false)
    {
        $invoiceItem = null;
        $invoice = $this->getInvoice();
        
        // initialize product id to sku mapping
        $productIdSkuMap = $this->getProductIdSkuMap($invoice->getAllItems());
        
        // attempt to match the invoice item
        foreach ($invoice->getAllItems() as $_item) {
            if (is_null($childSku)) {
                if ($sku != $productIdSkuMap[$_item->getProductId()]) {
                    continue;
                }
                if ($_item->getHasChildren() || $_item->getParentItemId()) {
                    continue;
                }
                $invoiceItem = $_item;
                break;
            } else {
                if ($childSku != $productIdSkuMap[$_item->getProductId()]) {
                    continue;
                }
                $_orderItem = Mage::getModel('sales/order_item')->load($_item->getOrderItemId());
                if (!$_orderItem->getParentItemId()) {
                    continue;
                }
                $_parentOrderItem = Mage::getModel('sales/order_item')->load($_orderItem->getParentItemId());
                if ($sku != $productIdSkuMap[$_parentOrderItem->getProductId()]) {
                    continue;
                }
                $invoiceItem = $returnChild 
                    ? $_item 
                    : $this->getInvoiceItemByOrderItemId($_orderItem->getParentItemId());
            }
        }
        
        return $invoiceItem;
    }
    
    /**
     * Retrieve an invoice item by order item id.
     *
     * @param int $orderItemId
     * @return Mage_Sales_Model_Order_Invoice_Item 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getInvoiceItemByOrderItemId($orderItemId)
    {
        if (is_null($this->_orderItemIdInvoiceItemMap)) {
            foreach ($this->getInvoice()->getAllItems() as $_item) {
                $this->_orderItemIdInvoiceItemMap[$_item->getOrderItemId()] = $_item;
            }
        }
        
        return $this->_orderItemIdInvoiceItemMap[$orderItemId];
    }
}