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
class Ampersand_Adminhtml_Helper_Image extends Mage_Core_Helper_Abstract
{
    public function getProductImageUrl(Mage_Catalog_Model_Product $product, $configPath,
        $code = null
    ) {
        if (!$image = $this->getConfiguredImage($product, $configPath, $code)) {
            return null;
        }

        return (string) $image;
    }

    public function getProductImageUrls(Mage_Catalog_Model_Product $product, $configPath)
    {
        $urls = array();

        foreach ($this->_getImageConfigData($configPath) as $_imageConfigData) {
            $_code = $this->_getImageCode($_imageConfigData);
            $urls[$_code] = (string) $this->_getPreparedImage($product, $_imageConfigData);
        }

        return $urls;
    }

    public function getConfiguredImage(Mage_Catalog_Model_Product $product, $configPath,
        $code = null
    ) {
        if (!$configData = $this->_getImageConfigData($configPath, $code)) {
            return null;
        }

        return $this->_getPreparedImage($product, $configData);
    }

    protected function _getImageConfigData($configPath, $code = null)
    {
        $configData = Mage::getStoreConfig($configPath);
        if (is_string($configData)) {
            $configData = unserialize($configData);
        }

        if (!is_array($configData)) {
            return array();
        }

        if (!is_null($code)) {
            if (!array_key_exists($code, $configData)) {
                return array();
            }

            return $configData[$code];
        }

        return $configData;
    }

    protected function _getImageCode($configData)
    {
        if (!is_array($configData) || !array_key_exists('code', $configData)) {
            return null;
        }

        return $configData['code'];
    }

    protected function _getPreparedImage(Mage_Catalog_Model_Product $product, $configData)
    {
        $config = $this->_prepareImageConfig($configData);

        $image = $this->_initProductImage($product, $config);

        return $this->_prepareProductImage($image, $config);
    }

    protected function _prepareImageConfig($configData)
    {
        if (!is_array($configData)) {
            Mage::throwException('Image config data must be an array');
        }

        return new Ampersand_Object($configData);
    }

    protected function _initProductImage(Mage_Catalog_Model_Product $product, Ampersand_Object $config)
    {
        $image = Mage::helper('catalog/image')->init($product, $config->getAttribute());

        return $image;
    }

    protected function _prepareProductImage(Mage_Catalog_Helper_Image $image, Ampersand_Object $config)
    {
        $width = $config->getWidth();
        $height = $config->getHeight();
        if (strlen($width) || strlen($height)) {
            $image->resize($width, $height);
        }

        $keepFrame = (bool) $config->getUseFrame();
        $image->keepFrame($keepFrame);

        $backgroundColour = $config->getBackground();
        if (strlen($backgroundColour)) {
            $image->backgroundColor($this->_hexToRgb($backgroundColour));
        }

        return $image;
    }

    protected function _hexToRgb($hex)
    {
        $hex = trim($hex, '# ');

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));

        return array($red, $green, $blue);
    }
}