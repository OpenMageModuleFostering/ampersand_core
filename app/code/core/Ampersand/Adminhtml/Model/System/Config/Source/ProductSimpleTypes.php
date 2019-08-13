<?php
class Ampersand_Adminhtml_Model_System_Config_Source_ProductSimpleTypes
{
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function toOptionArray()
    {
        $typeLabels = Mage_Catalog_Model_Product_Type::getOptionArray();
        $compositeTypeIds = Mage_Catalog_Model_Product_Type::getCompositeTypes();
        asort($typeLabels);
        
        $options = array();
        
        foreach ($typeLabels as $_typeId => $_label) {
            if (!in_array($_typeId, $compositeTypeIds)) {
                $options[] = array(
                    'value' => $_typeId,
                    'label' => $_label,
                );
            }
        }

        return $options;
    }
}