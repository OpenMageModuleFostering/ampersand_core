<?php
class Ampersand_System_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Check if the object already exists and set its data if so
     *
     * @param mixed $object Object to check if exists
     * @param string $modelName Shortname of the type of model
     * @param string $loadField Field name to try loading by
     * @return Ampersand_System_Helper_Store
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function loadIfExists($object, $modelName, $loadField)
    {
        if ((!$object->getIsObjectLoaded())
                && ($model = Mage::getModel($modelName))
                && ($model->load($object->getData($loadField), $loadField))
                && ($model->getId())) {
            $mergedData = array_merge($model->getData(), $object->getData());
            $object->addData($mergedData);
            $object->setIsObjectLoaded(true);
        }

        return $this;
    }

    /**
     * Ensure all required fields are populated and valid
     *
     * @param mixed $object Object to validate
     * @param array $requiredFields Array of required field => default value
     * @return Ampersand_System_Helper_Store
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function validate($object, $requiredFields)
    {
        $errors = array();

        foreach ($requiredFields as $_field => $_defaultValue) {
            if (is_null($object->getDataUsingMethod($_field))) {
                if (is_null($_defaultValue)) {
                    $errors[] = $_field;
                } else {
                    $object->setData($_field, $_defaultValue);
                }
            }
        }

        if ($errors) {
            Mage::throwException('Missing or invalid data: ' . implode(', ', $errors));
        }

        return $this;
    }
}