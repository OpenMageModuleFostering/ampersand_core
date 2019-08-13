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
class Ampersand_Xml
{
    const NULL_NAMESPACE    = 'http://null/';

    /**
     * Creates a new XML element without creating warnings if invalid XML is provided.
     * Invalid XML will cause an exception to be thrown
     *
     * @param string $source XML string or url to a file containing XML
     * @param bool $isUrl OPTIONAL true => the source is a URL, false => source is XML
     * @param string $class OPTIONAL Class to instantiate
     * @return mixed The new XML element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function factory($source, $isUrl = false, $class = null)
    {
        if (is_null($class)) {
            $class = 'Ampersand_Xml_Element';
        }

        return @new $class($source, null, $isUrl);
    }

    /**
     * Checks whether the argument is a valid XML name
     *
     * @param mixed $name
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function isValidName($name)
    {
        if (!is_string($name)) {
            return false;
        }

        $namePattern = self::getNamePattern();

        return (bool) preg_match("/^$namePattern$/i", $name);
    }

    /**
     * Checks whether the argument is a valid compound
     *
     * @param mixed $compound
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function isValidCompound($compound)
    {
        if (!is_string($compound)) {
            return false;
        }

        $namePattern = self::getNamePattern();

        return (bool) preg_match("/^$namePattern(#[0-9]+)?$/i", $compound);
    }

    /**
     * Builds a compound string using an element name, a namespace prefix and an
     * index. This can be used to ensure a unique key per element when retrieving
     * an array of child elements for a given element
     *
     * @param string $name An element name. If $prefix is provided then this
     * variable should not include a namespace prefix
     * @param string $prefix OPTIONAL A namespace prefix
     * @param int $index OPTIONAL If an element has siblings which share the same
     * name and prefix then an index can be used to differentiate between them
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function buildCompound($name, $prefix = null, $index = null)
    {
        if (!is_null($prefix)) {
            $name = "$prefix:$name";
        }
        if (!is_null($index)) {
            $name = "$name#$index";
        }

        return $name;
    }

    /**
     * Extracts the name part from a compound string
     *
     * @param string $compound
     * @param bool $includePrefix OPTIONAL Include the namespace prefix in the
     * returned name
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function getNameFromCompound($compound, $includePrefix = true)
    {
        if (!$includePrefix) {
            // Strip prefix
            if (false !== ($needlePos = strpos($compound, ':'))) {
                $compound = substr($compound, 1 + $needlePos);
            }
        }

        // Strip index
        if (false !== ($needlePos = strpos($compound, '#'))) {
            $compound = substr($compound, 0, $needlePos);
        }

        return $compound;
    }

    /**
     * Extracts the prefix part from a compound string
     *
     * @param string $compound
     * @return mixed Prefix as string; null if no prefix in the compound
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function getPrefixFromCompound($compound)
    {
        $prefix = null;

        if (false !== ($needlePos = strpos($compound, ':'))) {
            $prefix = substr($compound, 0, $needlePos);
        }

        return $prefix;
    }

    /**
     * Extracts the index part from a compound string
     *
     * @param string $compound
     * @return mixed Index as an int; null if no index is in the compound
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function getIndexFromCompound($compound)
    {
        $index = null;

        if (false !== ($needlePos = strpos($compound, '#'))) {
            $index = substr($compound, 1 + $needlePos);
        }

        return $index;
    }

    public static function removeDeclaration($xml)
    {
        $declaration = self::getDeclarationPattern();

        return preg_replace("#^(\s*)(?:$declaration)#", "$1", $xml);
    }
    
    public static function addDoctype($xml, $doctype)
    {
        $declaration = self::getDeclarationPattern();

        return preg_replace("#^(\s*{$declaration})#", '$1' . PHP_EOL . $doctype, $xml);
    }

    public static function getDeclarationPattern()
    {
        return '<\?xml[^>]+\?>';
    }

    public static function getNodePattern()
    {
        $namePattern = self::getNamePattern();

        $attribute = $namePattern . '\s*=\s*"[^"]*"';
        $attributes = "(?:\s*$attribute)*";

        return "<(?P<node>(?P<name>$namePattern)\s*(?P<attributes>$attributes)\s*)(?P<self_close>/)?>";
    }

    public static function getRootNode($xml, $part = 'node')
    {
        $declaration = self::getDeclarationPattern();
        $element = self::getNodePattern();
        preg_match("#^\s*(?:$declaration)?\s*$element#i", $xml, $matches);

        if (isset($matches[$part])) {
            return $matches[$part];
        }

        return null;
    }

    public static function removeNamespaces($xml, array $namespacesToRemove = null)
    {
        $attributesString = '';

        $attributes = self::getAttributes($xml);
        foreach ($attributes as $_name => $_value) {
            if (self::_shouldKeepAttribute($_name, $_value, $namespacesToRemove)) {
                $attributesString .= " $_name=\"$_value\"";
            }
        }

        $declaration = self::getDeclarationPattern();
        $element = self::getNodePattern();
        $nodeName = self::getRootNode($xml, 'name');
        $node = self::getRootNode($xml);
        $suffix = self::getRootNode($xml, 'self_close');

        return preg_replace("#^(\s*(?:$declaration)?\s*)$element#", "$1<{$nodeName}{$attributesString}{$suffix}>", $xml);
    }

    protected static function _shouldKeepAttribute($fullName, $value, array $namespacesToRemove = null)
    {
        $prefix = self::getPrefixFromCompound($fullName);

        if ('xmlns' === $fullName) {
            return $value !== Ampersand_XML::NULL_NAMESPACE;
        }
        if ('xmlns' !== $prefix) {
            return true;
        }

        if ($namespacesToRemove) {
            $name = !$prefix ? '' : self::getNameFromCompound($fullName, false);
            if (!in_array($name, $namespacesToRemove) && !in_array($value, $namespacesToRemove)) {
                return true;
            }
        }

        return false;
    }

    public static function getNamePattern($prefix = null)
    {
        if ($prefix) {
            return "$prefix:[-_:\.a-zA-Z0-9]*";
        }

        return '[_:a-zA-Z][-_:\.a-zA-Z0-9]*';
    }

    public static function getAttributes($xml, $prefix = null)
    {
        $attributes = array();

        if ($attributesString = self::getRootNode($xml, 'attributes')) {
            $namePattern = self::getNamePattern($prefix);
            $attributePattern = "(?P<names>$namePattern)\s*=\s*\"(?P<values>[^\"]*)\"";

            preg_match_all("#$attributePattern#", $attributesString, $matches);

            if ($matches['names']) {
                $attributes = array_combine($matches['names'], $matches['values']);
            }
        }

        return $attributes;
    }

    public static function rebuildElement(Ampersand_Xml_Element $element, array $children)
    {
        $name = $element->getName();
        $attributes = $element->getAttributes();

        foreach ($element->getNamespaces(true) as $_prefix => $_namespace) {
            if ($_prefix) {
                $attributes["xmlns:$_prefix"] = $_namespace;
            }
        }

        $filteredChildren = array();

        foreach ($children as $_child) {
            $_filtered = self::removeDeclaration($_child);
            if (strlen($_filtered)) {
                if ($_childNamespaces = self::getAttributes($_filtered, 'xmlns')) {
                    $_filtered = self::removeNamespaces($_filtered);
                    $attributes = array_merge($_childNamespaces, $attributes);
                }

                $filteredChildren[] = $_filtered;
            }
        }

        $attributesString = self::attributesToString($attributes);
        $innerXml = implode("\n", $filteredChildren);

        $xml = "<{$name}{$attributesString}>$innerXml</$name>";

        return $element->getNewInstance($xml);
    }

    public static function attributesToString($attributes)
    {
        $attributesString = '';

        foreach ($attributes as $_name => $_value) {
            $attributesString .= " $_name=\"$_value\"";
        }

        return $attributesString;
    }

    public static function dataToXml($data, $rootName = null, $addCdata = true, $formatted = false)
    {
        $xml = self::_dataToXml($data, $rootName, $addCdata, $formatted);

        if (!empty($rootName) && $formatted) {
            try {
                $xmlObject = Ampersand_Xml::factory($xml);
                $xml = $xmlObject->asFormattedXml();
            } catch (Exception $e) {
                // $xml does not contain a valid XML string
            }
        }

        return $xml;
    }

    protected static function _dataToXml($data, $rootName, $addCdata)
    {
        if (!$data) {
            if (!empty($rootName)) {
                return "<$rootName/>\n";
            }

            return '';
        }

        if (!is_array($data)) {
            if ($addCdata) {
                $xml = "<![CDATA[$data]]>";
            } else {
                $xml = self::escape($data);
            }

            if (!empty($rootName)) {
                $xml = "<$rootName>$xml</$rootName>"."\n";
            }

            return $xml;
        }

        $_unnamedFields = array();
        $_namedFields = array();

        foreach ($data as $_fieldName => $_fieldValue) {
            if (preg_match('/^\d+$/', $_fieldName)) {
                $_unnamedFields[] = $_fieldValue;
            } else {
                $_namedFields[$_fieldName] = $_fieldValue;
            }
        }

        $xml = '';

        if ($_namedFields) {
            if (!empty($rootName)) {
                $xml.= "<$rootName>\n";
            }

            foreach ($_namedFields as $_fieldName => $_fieldValue) {
                $xml .= self::dataToXml($_fieldValue, $_fieldName, $addCdata);
            }

            if (!empty($rootName)) {
                $xml.= "</$rootName>\n";
            }
        }

        if ($_unnamedFields && !empty($rootName)) {
            foreach ($_unnamedFields as $_fieldValue) {
                $xml .= self::dataToXml($_fieldValue, $rootName, $addCdata);
            }
        }

        return $xml;
    }

    public static function escape($string)
    {
        return str_replace(
            array('&', '"', "'", '<', '>'),
            array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'),
            $string
        );
    }

    public static function isValidXml($string)
    {
        try {
            self::factory($string);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}