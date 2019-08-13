<?php
class Ampersand_Api_Model_Customer_Address extends Mage_Customer_Model_Address_Api
{
    /**
     * @param Mage_Customer_Model_Address_Abstract $address
     * @param array $filter
     * @return string 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getAllowedAttributes($address, array $filter = null)
    {
        $attributes = parent::getAllowedAttributes($address, $filter);
        
        $attributes['should_ignore_validation'] = null;
        
        return $attributes;
    }
}