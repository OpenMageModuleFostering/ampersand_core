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
class Ampersand_Api_Model_Sales_Order_Creditmemo
{
    public function create($orderIncrementId, array $creditMemo) {
        $creditmemoApi = Mage::getModel('sales/order_creditmemo_api');
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

        $qtys = array();
        foreach($creditMemo['items'] as $_itemData) {
            $orderItem = $order
                ->getItemsCollection()
                ->addFieldToFilter('sku', $_itemData['sku'])
                ->getFirstItem();

            $qtys[$orderItem->getId()] = $_itemData['qty'];
        }

        $creditmemoIncrementId = $creditmemoApi->create($orderIncrementId, $qtys);

        if (array_key_exists('created_at', $creditMemo)) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoIncrementId, 'increment_id');
            $creditmemo->setCreatedAt($creditMemo['created_at']);
            $creditmemo->save();
        }

        return $creditmemoIncrementId;
    }
}