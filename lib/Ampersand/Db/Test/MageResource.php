<?php
class Ampersand_Db_Test_MageResource extends Mage_Core_Model_Resource_Type_Db_Pdo_Mysql
{
    /**
     * Causes Magento to use our test database adapter instead of its standard PDO adapter. This
     * will not have any affect unless capturing or fetching is enabled in the test adapter
     *
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _getDbAdapterClassName()
    {
        return 'Ampersand_Db_Test_Adapter';
    }
}