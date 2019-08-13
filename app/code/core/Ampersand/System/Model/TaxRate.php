<?php
class Ampersand_System_Model_TaxRate extends Mage_Tax_Model_Calculation_Rate
{
    /**
     * Shortname of this class
     * 
     * @var string $_modelName
     */
    protected $_modelName = 'tax/calculation_rate';

    /**
     * Field to load by when checking if already exists
     *
     * @var string $_loadField
     */
    protected $_loadField = 'code';

    /**
     * Database column name of the id field
     *
     * @var string $_idField
     */
    protected $_idField = 'tax_calculation_rate_id';

    /**
     * Required fields for saving this object.
     * If a default value is allowed then provide a non-null value
     *
     * @var array $_requiredFields
     */
    protected $_requiredFields = array(
        'code' => null,
        'tax_country_id' => null,
        'rate' => 0,
    );
    
    /**
     * If these methods are called with setXxx, addXxx, setData(xxx, xxx) etc.
     * setDataUsingMethod() will always be forced.
     *
     * @var array $_forceSetDataUsingMethod
     */
    protected $_forceSetDataUsingMethod = array(
    );

    /**
     * By setting the ID of a Store, we attempt to load it. If we are unable
     * to load the Store, unset the id field as we want a new Sroup to be created.
     *
     * @param type $id int
     * @return Ampersand_System_Model_Store 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function setId($id)
    {
        parent::setData($this->_idField, $id);
        Mage::helper('ampersand_system')->loadIfExists($this, $this->_modelName, $this->_idField);

        if (!$this->getIsObjectLoaded()) {
            parent::unsData($this->_idField);
        }
        return $this;
    }
    
    /**
     * Ensure we force the method use, rather than setData, where appropriate
     *
     * @param mixed $key
     * @param mixed $value
     * @return Ampersand_System_Model_Store
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $_key => $_value) {
                $this->_setDataUsingMethod($_key, $_value);
            }
        } else {
            $this->_setDataUsingMethod($key, $value);
        }

        return $this;
    }
    
    /**
     * Ensure we force the method use, rather than setData, where appropriate
     *
     * @param string $key
     * @param mixed $value
     * @return Ampersand_System_Model_Store
     */
    protected function _setDataUsingMethod($key, $value = null)
    {
        if (in_array($key, $this->_forceSetDataUsingMethod)) {
            $this->setDataUsingMethod($key, $value);
        } else {
            parent::setData($key, $value);
        }

        return $this;
    }
    
    /**
     * Load if exists, validate and save the object
     *
     * @return Ampersand_System_Model_Store
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _beforeSave()
    {
        Mage::helper('ampersand_system')->loadIfExists($this, $this->_modelName, $this->_loadField);
        Mage::helper('ampersand_system')->validate($this, $this->_requiredFields);

        return parent::_beforeSave();
    }
    
//
//    public function addNewTaxRate()
//    {
//        $taxModel = Mage::getModel('');
//        $taxRate = $taxModel->loadByCode('UK');
//        $taxRate->setData('code', 'UK');
//        $taxRate->setData('tax_country_id', 'GB');
//        $taxRate->setData('zip_is_range', false);
//        $taxRate->setData('rate', 20);
//        $taxRate->save();
//    }

}