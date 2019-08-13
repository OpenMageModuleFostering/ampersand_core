<?php
/**
 * Ampersand IT Magento Suite
 *
 * @category    Ampersand_Magento
 * @package     Ampersand_Adminhtml
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Magento
 * @package     Ampersand_Adminhtml
 * @subpackage  Helper
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Adminhtml_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $configPath 
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getConfigUrl($configPath)
    {
        $parts = explode('/', $configPath);
        if (!$section = reset($parts)) {
            return null;
        }
        
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array(
            'section' => $section,
        ));
    }
}