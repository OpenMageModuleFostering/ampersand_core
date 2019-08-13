<?php
/**
 * example data
 * $shipmentData = array(
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
class Ampersand_Api_Model_Sales_Order_Shipment
{
    public function create($orderIncrementId, array $shipmentData) {
        $shipmentApi = Mage::getModel('sales/order_shipment_api');
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        $qtys = array();
        foreach($shipmentData['items'] as $_itemData) {
            $orderItem = $order
                ->getItemsCollection()
                ->addFieldToFilter('sku', $_itemData['sku'])
                ->getFirstItem();

            $qtys[$orderItem->getId()] = $_itemData['qty'];
        }

        $shipmentIncrementId = $shipmentApi->create($orderIncrementId, $qtys);

        if (array_key_exists('created_at', $shipmentData)) {
            $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);
            $shipment->setCreatedAt($shipmentData['created_at']);
            $shipment->save();
        }

        return $shipmentIncrementId;
    }
}