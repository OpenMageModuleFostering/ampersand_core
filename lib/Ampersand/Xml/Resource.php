<?php
/**
 * Ampersand IT Library
 *
 * @category    Ampersand_Library
 * @package     Ampersand_Xml
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Library
 * @package     Ampersand_Xml
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Xml_Resource
{
    const ATTRIBUTE = 'resource_id';

    protected static $_registry = array();
    protected static $_nextId = 0;

    protected $_id;
    protected $_value;

    public static function factory($value, Ampersand_Xml_Element $element)
    {
        $resource = self::_getNewResourceInstance();

        $resourceId = self::$_nextId++;
        self::$_registry[$resourceId] = $resource;

        $resource->setId($resourceId)
                 ->setValue($value)
                 ->assignToElement($element)
        ;

        return $resource;
    }
    
    public static function reset()
    {
        self::$_registry = array();
        self::$_nextId = 0;
    }

    protected static function _getNewResourceInstance()
    {
        return new Ampersand_Xml_Resource();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;

        return $this;
    }

    public static function clearElement(Ampersand_Xml_Element $element)
    {
        $element->removeAttribute(self::ATTRIBUTE);
    }

    public function assignToElement(Ampersand_Xml_Element $element)
    {
        $element->unsetValue()
                ->setAttribute(self::ATTRIBUTE, $this->getId())
        ;

        return $this;
    }

    public static function elementHasResource($element)
    {
        return !is_null(self::getElementResourceId($element));
    }

    public static function getResource($id)
    {
        if (array_key_exists($id, self::$_registry)) {
            return self::$_registry[$id];
        }

        return null;
    }

    public static function getElementResourceId($element)
    {
        if (!is_object($element)) {
            $render = Ampersand_Xml::factory($element);
        }

        return $element->getAttribute(self::ATTRIBUTE);
    }

    public static function getElementResource($element)
    {
        if (!self::elementHasResource($element)) {
            return null;
        }

        return self::getResource(self::getElementResourceId($element));
    }

    public static function getElementValue($element)
    {
        if (!$resource = self::getElementResource($element)) {
            return null;
        }

        return $resource->getValue();
    }
}