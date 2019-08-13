<?php
/**
 * example data
 * $invoiceData = array(
 *     'items' => array(
 *         array(
 *             'sku' => 'test-product-1',
 *             'qty' => 3,
 *         ),
 *         array(
 *             'sku' => 'test-product-2',
 *             'qty' => 5,
 *         ),
 *     ),
 *     'created_at' => '2011-12-14 10:35:12',
 * );     
 */
class Ampersand_Api_Model_Sales_Order_Invoice
{
    public function create($orderIncrementId, array $invoiceData) {
        $invoiceApi = Mage::getModel('sales/order_invoice_api');
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        $qtys = array();
        foreach($invoiceData['items'] as $_itemData) {
            $orderItem = $order
                ->getItemsCollection()
                ->addFieldToFilter('sku', $_itemData['sku'])
                ->getFirstItem();

            $qtys[$orderItem->getId()] = $_itemData['qty'];
        }

        $invoiceIncrementId = $invoiceApi->create($orderIncrementId, $qtys);

        if (array_key_exists('created_at', $invoiceData)) {
            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceIncrementId);
            $invoice->setCreatedAt($invoiceData['created_at']);
            $invoice->save();
        }

        return $invoiceIncrementId;
    }
}