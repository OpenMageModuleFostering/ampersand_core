<?php

class Ampersand_Api_Model_Review
{
    /*
      $reviewApi->create('JRMTEST2', array(
      'nickname' => 'review nickname', // required
      'title' => 'review title', // required
      'detail' => 'review detail', // required
      'status_id' => Mage_Review_Model_Review::STATUS_PENDING, // optional (default: Mage_Review_Model_Review::STATUS_APPROVED)
      'customer_id' => 18, // optional (default: guest)         // Use either customer_id
      'customer_email' => '1322135044@ampersandcommerce.com',   //         OR customer_email
      'created_at' => 123123123, //optional (default: current time) // optional
      'rating_id' => 4, // optional
      ), 1, array(
      'Price' => '4',
      'Value' => '5',
      'Quality' => '3',
      )
      );
     */

    public function create($sku, $reviewData, $storeId = null, $ratings = null)
    {
        $store = Mage::app()->getStore($storeId);

        if (!array_key_exists('customer_id', $reviewData)) {
            if (array_key_exists('customer_email', $reviewData)) {
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore($store)->getWebsiteId())
                    ->loadByEmail($reviewData['customer_email']);

                $reviewData['customer_id'] = $customer->getId();
            }
        }

        $product = Mage::getModel('catalog/product')->setStoreId($store->getId());
        $product->load($product->getIdBySku($sku));

        $review = Mage::getModel('review/review');
        $entityId = $review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE);
        $review
            ->setEntityId($entityId)
            ->setEntityPkValue($product->getId())
            ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setStoreId($store->getId())
            ->setStores(array($store->getId()))
            ->addData($reviewData);

        $review->save();

        // slight hack to set created_at
        if (array_key_exists('created_at', $reviewData)) {
            $review->setCreatedAt($reviewData['created_at']);
            $review->save();
        }
        if (is_array($ratings) && count($ratings) > 0) {
            foreach ($ratings as $ratingCode => $ratingValue) {
                $rating = Mage::getModel('rating/rating')->load($ratingCode, 'rating_code');
                if ($rating->getId()) {
                    $ratingOptions = $rating->getOptions();
                    foreach ($ratingOptions as $_ratingOption) {
                        if ($ratingValue == $_ratingOption->getValue()) {

                            /**
                             * Mage_Rating_Model_Mysql4_Rating_Option::addVote() checks the action
                             * type as an additional security feature in PE, so we need to fudge it
                             */
                            $currentAction = Mage::app()->getFrontController()->getAction();
                            $tempAction = new Mage_Adminhtml_Controller_Action(
                                    new Zend_Controller_Request_Http,
                                    new Zend_Controller_Response_Http,
                                    array()
                            );
                            Mage::app()->getFrontController()->setAction($tempAction);

                            $rating
                                ->setRatingId($rating->getId())
                                ->setReviewId($review->getId())
                                ->addOptionVote($_ratingOption->getId(), $product->getId());

                            // reset the frontcontroller action back how it was
                            Mage::app()->getFrontController()->setAction($currentAction);
                            break;
                        }
                    }
                }
            }
        }
        $review->aggregate();
    }

}