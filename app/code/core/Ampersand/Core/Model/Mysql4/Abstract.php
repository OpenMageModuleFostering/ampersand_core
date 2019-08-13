<?php
abstract class Ampersand_Core_Model_Mysql4_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Retrieves the connection configuration with the provided name, and opens a new  connection to
     * the database using that configuration. Useful when multiple database connections are needed
     * in order to avoid unwanted transaction rollbacks
     *
     * @param string $connectionConfigName OPTIONAL Defaults to core write connection
     * @return Varien_Db_Adapter_Pdo_Mysql 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getNewConnection($connectionConfigName = 'core_write')
    {
        $connConfig = Mage::getConfig()->getResourceConnectionConfig($connectionConfigName);
        $typeInstance = Mage::getSingleton('core/resource')
            ->getConnectionTypeInstance((string) $connConfig->type);
        
        return $typeInstance->getConnection($connConfig);
    }
}