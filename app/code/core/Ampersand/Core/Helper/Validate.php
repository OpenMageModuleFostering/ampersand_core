<?php
class Ampersand_Core_Helper_Validate extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve whether the provided variable is numeric.
     *
     * @param mixed $value
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function isNumeric($value)
    {
        return $this->isInt($value) || $this->isFloat($value);
    }
    
    /**
     * Retrieve whether the provided variable is an integer.
     * Leading zero's are permitted, as are minus numbers.
     * 
     * See test data for accepted and invalid formats.
     *
     * @param mixed $value
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function isInt($value)
    {
        return preg_match('/^\-*\d+$/', (string)$value) === 1;
    }
    
    /**
     * Retrieve whether the provided variable is a float.
     * At least one decimal place must be included.
     * Leading zero's are permitted, as are minus numbers.
     * 
     * See test data for accepted and invalid formats.
     *
     * @param mixed $value
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function isFloat($value)
    {
        return preg_match('/^\-*\d+\.\d+$/', (string)$value) === 1;
    }
}