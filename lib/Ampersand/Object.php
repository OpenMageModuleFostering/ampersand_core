<?php
/**
 * Ampersand IT Library
 *
 * @category    Ampersand_Library
 * @package     Ampersand
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Library
 * @package     Ampersand
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Object extends Varien_Object
{
    protected static $_singleton = null;
    
    public function getDataUnset($name = null)
    {
        $value = $this->getData($name);
        $this->unsetData($name);

        return $value;
    }

    public function getDataSet($name, $value = null)
    {
        $value = $this->getData($name);
        $this->setData($name, $value);

        return $value;
    }

    /*
     * Had to disable as completely different params to parent method causing E_STRICT errors.
     * - Joseph McDermott <joseph.mcdermott@ampersandcommerce.com
     * 
    public function toXml($rootName = 'item', $addCdata = true, $formatted = false)
    {
        return Ampersand_Xml::dataToXml($this->getData(), $rootName, $addCdata, $formatted);
    }
    */

    public function setDataUsingMethod($key, $args = array())
    {
        if (is_array($key)) {
            foreach ($key as $_key => $_args) {
                $this->setDataUsingMethod($_key, $_args);
            }
        } else {
            self::setDataAtPath($this, $key, $args);
        }

        return $this;
    }

    public static function camelize($name)
    {
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);

        return str_replace(' ', '', $name);
    }

    protected function _camelize($name)
    {
        return self::camelize($name);
    }
    
    /**
     * Proxy method to Varien_Object _underscore.
     *
     * @param string $name
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function underscore($name)
    {
        $object = self::getSingleton();
        
        return $object->_underscore($name);
    }

    public static function getDataAtPath($container, $fieldPath)
    {
        $fieldParts = explode('/', $fieldPath, 2);
        $firstField = $fieldParts[0];
        $fieldPath = array_key_exists(1, $fieldParts) ? $fieldParts[1] : null;
        
        $value = null;

        if (is_array($container) && array_key_exists($firstField, $container)) {
            $value = $container[$firstField];
        }

        if (is_object($container)) {
            $methodName = 'get' . self::camelize($firstField);
            $value = call_user_func(array($container, $methodName));
        }

        if ($fieldPath) {
            $value = self::getDataAtPath($value, $fieldPath);
        }

        return $value;
    }

    public static function setDataAtPath($container, $fieldPath, $value)
    {
        $fields = explode('/', $fieldPath);

        $containers = array(&$container);
        foreach ($fields as $_field) {
            $_container = &$containers[count($containers) - 1];

            if (is_object($_container)) {
                $containers[] = self::getDataAtPath($_container, $_field);
            } else {
                if (!is_array($_container)) {
                    array_pop($containers);
                    $containers[] = array();
                    $_container = &$containers[count($containers) - 1];
                }

                if (!array_key_exists($_field, $_container)
                        || (!is_object($_container[$_field]) && !is_array($_container[$_field]))) {
                    $_container[$_field] = array();
                }

                $containers[] = &$_container[$_field];
            }
        }

        $container = &$containers[0];

        /*
         * The last container is actually the current value of the field which we want to set --
         * disregard this container
         */
        array_pop($containers);
        $reversedFields = array_reverse($fields);

        foreach ($reversedFields as $_key => $_field) {
            $_container = &$containers[count($containers) - 1];
            array_pop($containers);

            if (is_object($_container)) {
                if ($_key) {
                    $value = $lastContainer;
                }

                $methodName = 'set' . self::camelize($_field);
                call_user_func(array($_container, $methodName), $value);
                break;
            } else if (!$_key) {
                $_container[$_field] = $value;
            }

            $lastContainer = &$_container;
        }

        return $container;
    }
    
    /**
     * Convert a nested array in to a nested Varien Object.
     *
     * @param array $data
     * @param string $class OPTIONAL Object class to be returned.
     * @return Ampersand_Object Unless different class specified.
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function arrayToObject(array $data, $class = 'Ampersand_Object')
    {
        foreach ($data as $_key => $_value) {
            if (is_array($_value)) {
                $data[$_key] = self::arrayToObject($_value);
            }
        }
        
        return new $class($data);
    }
    
    /**
     * Recursively merge two arrays (and their children arrays)
     *
     * @param array $array1
     * @param array $array2
     * @return array 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function arrayMergeRecursive($array1, $array2)
    {
        if (!is_array($array2)) {
            return $array1;
        }
        
        foreach ($array1 as $_field => $_value) {
            if (!array_key_exists($_field, $array2)) {
                continue;
            }
            
            if (is_array($_value)) {
                $array1[$_field] = self::arrayMergeRecursive($_value, $array2[$_field]);
            } else {
                $array1[$_field] = $array2[$_field];
            }
            
            unset($array2[$_field]);
        }
        
        $array1 = array_merge($array1, $array2);
        
        return $array1;
    }
    
    /**
     * Recursively merges data to the instance's data array
     *
     * @param array $data
     * @return Ampersand_Object 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function addDataRecursive($data)
    {
        $this->_data = self::arrayMergeRecursive($this->_data, $data);
        return $this;
    }
    
    /**
     * Retreive singleton object of self.
     *
     * @return Ampersand_Object
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function getSingleton()
    {
        if (is_null(self::$_singleton)) {
            self::$_singleton = new Ampersand_Object;
        }
        
        return self::$_singleton;
    }
}