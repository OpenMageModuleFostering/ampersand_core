<?php
class Ampersand_Api_Model_Rating
{
    public function create($ratingCode, $storeIds = array())
    {
        $rating = Mage::getModel('rating/rating')->load($ratingCode, 'rating_code');
        if ($rating->getId()) {
            return;
        }
        
        $stores = array(0);
        foreach ($storeIds as $_storeId) {
            $stores[] = Mage::app()->getStore($_storeId)->getId();
        }
        
        $entityId = $rating->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE);
        
        $rating
            ->setRatingCode($ratingCode)
            ->setStores($stores)
            ->setEntityId($entityId);

        $rating->save();
        
        $options = array(1,2,3,4,5);
        foreach ($options as $_optionValue) {
            Mage::getModel('rating/rating_option')
                ->setCode($_optionValue)
                ->setValue($_optionValue)
                ->setRatingId($rating->getId())
                ->setPosition($_optionValue)
                ->save();
        }
    }   
}