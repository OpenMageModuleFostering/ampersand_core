<?php
class Ampersand_Core_Model_Versioning
{
    /**
     * Editions of Magento.
     */
    const EDITION_CE = 'ce';
    const EDITION_EE = 'ee';
    
    /**
     * Compare Magento edition and version to the current version.
     *
     * @param string $requiredEdition
     * @param string $requiredVersion
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function compare($requiredEdition, $requiredVersion)
    {
        $actualEdition = $this->getEdition();
        if ($requiredEdition != $actualEdition) {
            return false;
        }
        
        $actualVersion = $this->getVersion();
        $result = version_compare($requiredVersion, $actualVersion);
        
        // required version is lower than the actual version
        if ($result === -1) {
            return true;
        }
        
        // the two versions are the same
        if ($result === 0) {
            return true;
        }
        
        // required version is higher than the actual version
        if ($result === 1) {
            return false;
        }
        
        // should never get here, but safer to assume the worst
        return false;
    }
    
    /**
     * Retreive the current Magento edition.
     *
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getEdition()
    {
        $edition = self::EDITION_CE;
        
        if ($this->isModuleEnabled('Enterprise_Enterprise')) {
            $edition = self::EDITION_EE;
        }
        
        /**
         * @todo handle PE or other editions
         */
        
        return $edition;
    }
    
    /**
     * Retrieve the current Magento version.
     *
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getVersion()
    {
        $versionData = Mage::getVersionInfo();
        
        $version = '';
        $version .= $versionData['major'] . '.';
        $version .= $versionData['minor'] . '.';
        $version .= $versionData['revision'] . '.';
        $version .= $versionData['patch'] . '.';
        
        $version = rtrim($version, '.');
        
        return $version;
    }
    
    /**
     * This is a copy of Mage_Core_Helper_Abstract::isModuleEnabled().
     *
     * @param string $moduleName
     * @return bool 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function isModuleEnabled($moduleName)
    {
        if (!Mage::getConfig()->getNode('modules/' . $moduleName)) {
            return false;
        }
        
        $isActive = Mage::getConfig()->getNode('modules/' . $moduleName . '/active');
        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }
        
        return true;
    }
}