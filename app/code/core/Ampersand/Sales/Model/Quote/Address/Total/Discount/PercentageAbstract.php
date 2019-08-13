<?php
/**
Usage:
======

Add a new total to config.xml:
<config>
    <global>
        <sales>
            <quote>
                <totals>
                    <unique_code_for_your_total>
                        <!-- model defined below should extend this class -->
                        <class>namespace/model</class>
                        <after>discount</after>
                        <before>grand_total</before>
                    </unique_code_for_your_total>
                </totals>
            </quote>
        </sales>
    </global>
    <default>
        <sales>
            <totals_sort>
                <unique_code_for_your_total>21</unique_code_for_your_total>
            </totals_sort>
        </sales>
    </default>
</config>

Add a new total sort to system.xml:
<config>
    <sections>
        <sales>
            <groups>
                <totals_sort>
                    <fields>
                        <unique_code_for_your_total translate="label">
                            <label>Label for your Discount</label>
                            <frontend_type>text</frontend_type>
                            <sort_order>201</sort_order>
                            <show_in_default>1</show_in_default>
                            <show_in_website>1</show_in_website>
                            <show_in_store>0</show_in_store>
                        </unique_code_for_your_total>
                    </fields>
                </totals_sort>
            </groups>
        </sales>
    </sections>
</config>

======
 * 
 */
abstract class Ampersand_Sales_Model_Quote_Address_Total_Discount_PercentageAbstract
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Collect address discount amount.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Ampersand_Sales_Model_Quote_Address_Total_Discount_PercentageAbstract
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        foreach ($items as $_item) {
            if ($_item->getParentItemId()) {
                continue;
            }

            if ($_item->getHasChildren() && $_item->isChildrenCalculated()) {
                foreach ($_item->getChildren() as $_child) {
                    $this->_processItem($_child);
                    $this->_aggregateItemDiscount($_child);
                }
            } else {
                $this->_processItem($_item);
                $this->_aggregateItemDiscount($_item);
            }
        }
        
        return $this;
    }
    
    /**
     * Add discount total information to address.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Ampersand_Sales_Model_Quote_Address_Total_Discount_PercentageAbstract
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getData($this->getCode());
        
        if (!empty($amount)) {
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $this->_getTitle(),
                'value' => $amount
            ));
        }
        
        return $this;
    }
    
    /**
     * Retrieve the title of the discount.
     *
     * @return string
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    abstract protected function _getTitle();
    
    /**
     * Retrieve the value of the discount.
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    abstract protected function _getDiscountValue(Mage_Sales_Model_Quote_Item_Abstract $item);

    /**
     * Quote item discount calculation process.
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return Ampersand_Sales_Model_Quote_Address_Total_Discount_PercentageAbstract
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _processItem(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $customDiscountValue = $this->_getDiscountValue($item);
        $discountPercent = min(100, $customDiscountValue);
        $discountPercentMultiplier = $discountPercent / 100;
        if ($discountPercent <= 0) {
            return $this;
        }
        
        // retrieve the quantity of this item to discount
        $qty = $this->_getItemQty($item);
        if ($qty <= 0) {
            return $this;
        }
        
        // retrieve the original price of the item, before any discounts were applied
        $originalPrice = $this->_getItemOriginalPrice($item);
        $baseOriginalPrice = $this->_getItemBaseOriginalPrice($item);
        if ($originalPrice <= 0) {
            return $this;
        }
        
        // calculate the total discount percentage
        $discountPercent = min(100, $item->getDiscountPercent() + $discountPercent);
        $item->setDiscountPercent($discountPercent);
        
        // calculate the total discount amount
        $discountAmount = ($qty * $originalPrice) * $discountPercentMultiplier;
        $discountAmount = $item->getQuote()->getStore()->roundPrice($discountAmount);
        $item->setData($this->getCode(), $discountAmount);
        $discountAmount = $item->getDiscountAmount() + $discountAmount;
        $item->setDiscountAmount($discountAmount);
        
        // calculate the total base discount amount
        $baseDiscountAmount = ($qty * $baseOriginalPrice) * $discountPercentMultiplier;
        $baseDiscountAmount = $item->getQuote()->getStore()->roundPrice($baseDiscountAmount);
        $item->setData("base_{$this->getCode()}", $baseDiscountAmount);
        $baseDiscountAmount = $item->getBaseDiscountAmount() + $baseDiscountAmount;
        $item->setBaseDiscountAmount($baseDiscountAmount);

        return $this;
    }

    /**
     * Return item quantity to apply discount to.
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return int
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getItemQty(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        return $item->getTotalQty();
    }

    /**
     * Return item original price.
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getItemOriginalPrice(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        return $this->_getApplyDiscountAfterTax($item) 
            ? $item->getPriceInclTax() 
            : $item->getPrice();
    }

    /**
     * Return item base original price.
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getItemBaseOriginalPrice(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        return $this->_getApplyDiscountAfterTax($item) 
            ? $item->getBasePriceInclTax() 
            : $item->getBasePrice();
    }
    
    /**
     * Retrieve whether to apply discount before(false) or after(true) tax.
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return bool
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getApplyDiscountAfterTax(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        return true;
    }
    
    /**
     * Aggregate item discount information to address data and related properties
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return Ampersand_Sales_Model_Quote_Address_Total_Discount_PercentageAbstract
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _aggregateItemDiscount(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        // update the amounts associated with this address
        $this->_addAmount(-$item->getData($this->getCode()));
        $this->_addBaseAmount(-$item->getData("base_{$this->getCode()}"));
        
        // update internal property so we can display discount amount to customer
        $addressCustomDiscount = $this->_getAddress()->getData($this->getCode());
        $updatedAddressCustomDiscount = $addressCustomDiscount - $item->getData($this->getCode());
        $this->_getAddress()->setData($this->getCode(), $updatedAddressCustomDiscount);
        
        return $this;
    }
}