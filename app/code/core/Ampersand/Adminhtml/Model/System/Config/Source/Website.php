<?php
class Ampersand_Adminhtml_Model_System_Config_Source_Website
{
    /** @var null|array */
    protected $_optionArray;
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function toOptionArray()
    {
        return Mage::getSingleton('ampersand_adminhtml/system_config_source_website')
            ->getOptionArray();
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getOptionArray()
    {
        if (is_null($this->_optionArray)) {
            $this->_optionArray = Mage::getResourceModel('core/website_collection')
                ->setFlag('load_default_website', true)
                ->setOrder('name', 'asc')
                ->toOptionArray();
        }
        
        return $this->_optionArray;
    }
}