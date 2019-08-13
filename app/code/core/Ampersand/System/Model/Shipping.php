<?php
class Ampersand_System_Model_Shipping
{
    /**
     * Update Tablerate shipping options from a CSV file
     *
     * @param type $moduleName Name of the module which contains the data file (Namespace_Module)
     * @param type $websiteId Website ID or code
     * @param type $condition Tablerate shipping condition. package_weight, package_value or package_qty
     * @param type $filepath Full path to the CSV file of table rates to import
     * @param string $shippingMethod Name of the module and resource model that define the shipping method, modulename/resource_path
     * @param string $fieldId configuable file path that resoucr model handles, this should correspond with $shippingMethod
     * @return Ampersand_System_Model_Shipping
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    public function updateTablerate($moduleName, $websiteId, $condition, $filepath, 
        $shippingMethod='shipping/carrier_tablerate',$fieldId='tablerate')
    {
        $filepath = Mage::getModuleDir('', $moduleName) . DS. 'data' . DS . $filepath;

        if (isset($_FILES['groups']['tmp_name'][$fieldId]['fields']['import']['value'])) {
            $origFilesTablerateImportValue = $_FILES['groups']['tmp_name'][$fieldId]['fields']['import']['value'];
        }
        
        $_FILES['groups']['tmp_name'][$fieldId]['fields']['import']['value'] = $filepath;
        
        $importParams = array(
            'groups' => array(
                $fieldId => array(
                    'fields' => array(
                        'condition_name' => array(
                            'value' => $condition,
                        ),
                    ),
                ),
            ),
            'scope_id' => $websiteId,
        );
        
        $importObject = new Varien_Object($importParams);
        
        Mage::helper('ampersand_system/store')->reinitStores();
        Mage::getResourceModel($shippingMethod)
            ->uploadAndImport($importObject);
        
        if (isset($origFilesTablerateImportValue)) {
            $_FILES['groups']['tmp_name'][$fieldId]['fields']['import']['value'] = $origFilesTablerateImportValue;
        }
        
        return $this;
    }
}