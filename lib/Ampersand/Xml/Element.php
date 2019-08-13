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
 * For php version >= 5.2.4
 *
 * @category    Ampersand_Library
 * @package     Ampersand_Xml
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Xml_Element extends Varien_Simplexml_Element
{
    const NODE_ID_FIELD     = '__ampersand_NODE_IDENTIFIER';
    const NULL_PREFIX       = '__ampersand_NULL_PREFIX';
    const COMPOUND_WILDCARD = '*';

    protected static $_nextNodeId = 0;

    /**
     * Gets the name of this element including the namespace prefix if set
     *
     * @param bool $includePrefix OPTIONAL Include the namespace prefix in the return value
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getName($includePrefix = true)
    {
        $name = parent::getName();
        if ($includePrefix) {
            $prefix = $this->getPrefix();
            if (!is_null($prefix)) {
                $name = "$prefix:$name";
            }
        }

        return $name;
    }

    /**
     * Gets the string value of this element
     *
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getValue()
    {
        if (Ampersand_Xml_Resource::elementHasResource($this)) {
            return Ampersand_Xml_Resource::getElementValue($this);
        }

        return (string) $this;
    }

    /**
     * Sets the string value of this element
     *
     * @param string $value
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function setValue($value, $forceResource = false)
    {
        if ($forceResource || (!is_null($value) && !is_scalar($value))) {
            Ampersand_Xml_Resource::factory($value, $this);
        } else {
            $this[0] = (string) $value;
            Ampersand_Xml_Resource::clearElement($this);
        }

        return $this;
    }

    /**
     * Unset the value of this element
     *
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function unsetValue()
    {
        $this->setValue('');

        return $this;
    }

    /**
     * Gets the namespace prefix which is prepended to the name of this element
     *
     * @param string $sourceXml OPTIONAL If specified, get the prefix for an element
     * specified as an XML string instead of using this element
     * @return mixed Prefix as string; null if no prefix is set
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getPrefix($sourceXml = null)
    {
        $prefix = null;

        if (is_null($sourceXml)) {
            $sourceXml = $this->asXml();
        }
        if (preg_match('#^\s*(?:<\?.*\?>\s*)?<(?P<prefix>[^/\s>]*):#', $this->asXml(), $matches)) {
            $prefix = $matches['prefix'];
        }

        return $prefix;
    }

    /**
     * Checks whether this element is aware of a particular namespace prefix
     *
     * @param string $prefix
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function hasPrefix($prefix)
    {
        $namespaces = $this->getDocNamespaces(true);
        foreach ($namespaces as $_prefix => $_namespace) {
            if ($prefix == $_prefix) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether this element is aware of a particular namespace
     *
     * @param string $namespace
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function hasNamespace($namespace)
    {
        $namespaces = $this->getDocNamespaces(true);
        foreach ($namespaces as $_namespace) {
            if ($namespace == $_namespace) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the namespace associated with the prefix which is prepended to the
     * name of this element
     *
     * @param string $sourceXml OPTIONAL If specified, get the namespace for an
     * element specified as an XML string instead of using this element
     * @return mixed Namespace as string; null if no namespace found
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getNamespace($sourceXml = null)
    {
        $namespace = null;

        $prefix = $this->getPrefix($sourceXml);
        if (!is_null($prefix)) {
            $namespace = $this->getNamespaceByPrefix($prefix);
        }

        return $namespace;
    }

    /**
     * Converts a namespace prefix to a namespace
     *
     * @param string $prefix
     * @return mixed Namespace as string; null if no namespace found for prefix
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getNamespaceByPrefix($prefix)
    {
        $namespaces = $this->getDocNamespaces(true);
        foreach ($namespaces as $_prefix => $_namespace) {
            if ($prefix == $_prefix) {
                return $_namespace;
            }
        }

        return null;
    }

    /**
     * Checks whether another XML element or XML string is equal to this object,
     * ignoring irrelevant whitespace
     *
     * @param mixed $xml XML element or XML string
     * @param bool $ignoreChildOrder OPTIONAL Ignore order of children in comparison
     * @param bool $ignoreAttributeOrder OPTIONAL Ignore order of attributes in comparison
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function equals($target, $ignoreChildOrder = true, $ignoreAttributeOrder = true)
    {
        if (!is_object($target)) {
            $target = $this->getNewInstance($target);
        } else if ($this === $target) {
            return true;
        }

        if ($this->getName() !== $target->getName()) {
            return false;
        }

        if (!$this->attributesEqual($target, $ignoreAttributeOrder)) {
            return false;
        }

        if (!$this->hasChildren() && !$target->hasChildren()) {
            return $this->getValue() == $target->getValue();
        }
        
        return $this->childrenEqual($target, $ignoreChildOrder);
    }

    /**
     * Checks whether the attributes of this element are equal to those within
     * an array or to those of another XML element which can be passed by reference
     * or specified as a valid XML string
     *
     * @param mixed $target Array of attribute (name => value) or XML element
     * containing attributes or XML string to be converted to such an element
     * @param bool $ignoreOrder OPTIONAL Ignore order of attributes in comparison
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function attributesEqual($target, $ignoreOrder = true)
    {
        if (is_string($target)) {
            $target = $this->getNewInstance($target);
        }
        if (is_object($target)) {
            $targetAttributes = $target->getAttributes();
        } else {
            $targetAttributes = $target;
        }

        $localAttributes = $this->getAttributes();

        if (count($localAttributes) != count($targetAttributes)) {
            return false;
        }

        if ($ignoreOrder) {
            return $targetAttributes == $localAttributes;
        }

        return $targetAttributes === $localAttributes;
    }

    /**
     * Checks whether the children of this element are equal to a set of elements
     * within an array or the children of another XML element which can be passed
     * by reference or specified as a valid XML string
     *
     * @param mixed $target Array of elements, an XML element containing
     * children to compare or an XML string to be converted to such an element
     * @param bool $ignoreOrder OPTIONAL Ignore order of children in comparison
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function childrenEqual($target, $ignoreOrder = true)
    {
        if (is_string($target)) {
            $target = $this->getNewInstance($target);
        }
        if (is_object($target)) {
            $targetElements = $target->getChildren();
        } else {
            $targetElements = $target;
        }

        $children = $this->getChildren();

        if (count($children) != count($targetElements)) {
            return false;
        }

        if ($ignoreOrder) {
            while (null !== ($_localChild = array_pop($children))) {
                foreach ($targetElements as $_key => $_foreignChild) {
                    if ($_localChild->equals($_foreignChild)) {
                        unset($targetElements[$_key]);
                        break;
                    }
                }

                // Check that one of the foreign nodes was matched in this loop
                if (count($children) != count($targetElements)) {
                    return false;
                }
            }
        } else {
            while (null !== ($_localChild = array_pop($children))) {
                $_foreignChild = array_pop($targetElements);
                if (!$_localChild->equals($_foreignChild)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks whether this element contains any attributes
     *
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function hasAttributes()
    {
        foreach ($this->getNamespaces() as $_prefix => $_namespace) {
            if ($this->attributes($_namespace)) {
                return true;
            }
        }

        return (bool) $this->attributes();
    }

    /**
     * Checks whether this element contains a given attribute
     *
     * @param string $name The name of the attribute to check
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function hasAttribute($name)
    {
        return !is_null($this->getAttribute($name));
    }

    /**
     * Gets all attribute values in this element
     *
     * @return array (string) name => (string) value
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getAttributes()
    {
        $attributes = array();
        foreach ($this->attributes() as $_key => $_value) {
            $attributes[$_key] = (string) $_value;
        }
        foreach ($this->getNamespaces() as $_prefix => $_namespace) {
            foreach ($this->attributes($_namespace) as $_key => $_value) {
                $attributes["$_prefix:$_key"] = (string) $_value;
            }
        }

        return $attributes;
    }

    /**
     * Gets the value of an attribute from this element
     *
     * @param string $name The name of the attribute to access
     * @return mixed String if attribute exists, null otherwise
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getAttribute($name)
    {
        $prefix = Ampersand_Xml::getPrefixFromCompound($name);
        $name = Ampersand_Xml::getNameFromCompound($name, false);

        @$value = $this->attributes($prefix, true)->$name;
        if (!is_null($value)) {
            $value = (string) $value;
        }

        return $value;
    }

    /**
     * Sets the value of an attribute within this element. If the attribute is not
     * yet set then this method will call appendAttribute
     *
     * @param string $name The name of the attribute to set
     * @param mixed $value The value to set against the attribute
     * @param string $namespace OPTIONAL If attribute uses a namespace which is
     * new to this element then it needs to be passed in here
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function setAttribute($name, $value, $namespace = null)
    {
        if (!$this->hasAttribute($name)) {
            $this->appendAttribute($name, $value, $namespace);
        } else {
            $namespace = $this->getNamespaceFromCompound($name);
            $name = Ampersand_Xml::getNameFromCompound($name, false);

            $this->attributes($namespace)->$name = (string) $value;
        }

        return $this;
    }

    /**
     * Adds an attribute & value to the end of this element's opening node if it
     * doesn't already exist
     *
     * @param string $name The name of the attribute to set
     * @param mixed $value The value to set against the attribute
     * @param string $namespace OPTIONAL If attribute uses a namespace which is
     * new to this element then it needs to be passed in here
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function appendAttribute($name, $value, $namespace = null)
    {
        if (!$this->hasAttribute($name)) {
            $compound = $name;
            if (!$namespace) {
                $namespace = $this->getNamespaceFromCompound($compound);
            }
            $name = Ampersand_Xml::getNameFromCompound($compound);

            $this->addAttribute($compound, $value, $namespace);
        }

        return $this;
    }

    /**
     * @todo IMPLEMENT ME
     */
    public function prependAttribute($name, $value, $namespace = null)
    {
        return $this;
    }

    /**
     * Removes an attribute from this element
     *
     * @param string $name The name of the attribute to remove
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function removeAttribute($name)
    {
        if ($this->hasAttribute($name)) {
            $prefix = Ampersand_Xml::getPrefixFromCompound($name);
            $name = Ampersand_Xml::getNameFromCompound($name, false);

            unset($this->attributes($prefix, true)->$name);
        }

        return $this;
    }

    /**
     * Removes all attributes from this element
     *
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function removeAttributes()
    {
        foreach ($this->getAttributes() as $_name => $_value) {
            $this->removeAttribute($_name);
        }

        return $this;
    }

    /**
     * Checks whether this element has any child elements
     *
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function hasChildren($debug = false)
    {
        foreach ($this->getNamespaces(true) as $_namespace) {
            foreach ($this->children($_namespace) as $_child) {
                return true;
            }
        }

        foreach ($this->children() as $_child) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether this element has a specific child element
     *
     * @param $compound
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function hasChild($compound)
    {
        return !is_null($this->getChild($compound));
    }

    /**
     * Gets a single child element of this element
     *
     * @param string $compound OPTIONAL Target which child to retrieve using a
     * compound string. If omitted, the first child will be returned
     * @return mixed
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getChild($compound = null)
    {
        if (!$children = $this->getChildren($compound, 1)) {
            return null;
        }

        return reset($children);
    }

    /**
     * Finds the unique compound for a given child element
     *
     * @param mixed $target Element as object or XML string
     * @return mixed Compound as a string or null if child not found
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getChildCompound($target)
    {
        foreach ($this->getChildren() as $_compound => $_child) {
            if ($_child->equals($target)) {
                return $_compound;
            }
        }

        return null;
    }

    /**
     * Gets child elements of this element in correct order
     *
     * @param string $compound OPTIONAL Target which children to retrieve using a
     * compound string. If omitted, all children are retrieved
     * @param int $limit OPTIONAL The maxiumum number of elements to return
     * @return array Associative, element compounds as keys, element objects as values
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getChildren($compound = null, $limit = null)
    {
        if (!is_null($compound)) {
            $name = Ampersand_Xml::getNameFromCompound($compound, false);
            $prefix = Ampersand_Xml::getPrefixFromCompound($compound);
            $index = Ampersand_Xml::getIndexFromCompound($compound);
        } else {
            $name = self::COMPOUND_WILDCARD;
            $prefix = self::COMPOUND_WILDCARD;
            $index = self::COMPOUND_WILDCARD;
        }

        if ($prefix === self::COMPOUND_WILDCARD) {
            $namespaces = $this->getNamespaces(true);
            if (array_key_exists('', $namespaces)) {
                unset($namespaces['']);
            }
            if (array_key_exists('xml', $namespaces)) {
                unset($namespaces['xml']);
            }

            $prefixes = array_keys($namespaces);

            $prefixes[] = '';
        } else {
            // Deliberate array(null) when no prefix specified
            $prefixes = array($prefix);
        }

        $children = array();
        $childCounts = array();
        if (1 === count($prefixes)) {
            $_prefix = reset($prefixes);
            $_children = array();
            if ($name === self::COMPOUND_WILDCARD) {
                $_children = $this->children($_prefix, true);
            } else if ($index === self::COMPOUND_WILDCARD) {
                $index = 0;
                while ((!$limit || $index < $limit) && null !== ($_child = $this->children($_prefix, true)->{$name}[$index])) {
                    $_children[] = $_child;
                    $index++;
                }
            } else {
                $_child = $this->children($_prefix, true)->{$name}[(int) $index];
                if (!is_null($_child)) {
                    $_children[] = $_child;
                }
            }

            if (!is_null($_children)) {
                foreach ($_children as $_node) {
                    $_name = $_node->getName();
                    if (!array_key_exists($_name, $childCounts)) {
                        $childCounts[$_name] = 0;
                    } else {
                        $_name = Ampersand_Xml::buildCompound($_name, null, ++$childCounts[$_name]);
                    }

                    $children[$_name] = $_node;

                    if ($limit && $limit <= count($children)) {
                        break;
                    }
                }
            }
        } else {
            $childNodes = array();
            foreach ($prefixes as $_prefix) {
                $compound = Ampersand_Xml::buildCompound($name, $_prefix, $index);

                foreach ($this->getChildren($compound, $limit) as $_node) {
                    $_nodeId = $_node->getIdentifier(true);
                    $childNodes[$_nodeId] = $_node;
                }
            }

            $string = $this->asXml();
            if (preg_match_all('/[<\s]' . self::NODE_ID_FIELD . '\s*="(?P<child_ids>\d+)"/', $string, $matches)) {
                $childIds = $matches['child_ids'];
                foreach ($childIds as $_childId) {
                    if (!array_key_exists($_childId, $childNodes)) {
                        continue;
                    }
                    
                    $_node = $childNodes[$_childId]->removeIdentifier();
                    $_name = $_node->getName();

                    if (!array_key_exists($_name, $childCounts)) {
                        $childCounts[$_name] = 0;
                    } else {
                        $_name = Ampersand_Xml::buildCompound($_name, null, ++$childCounts[$_name]);
                    }

                    $children[$_name] = $_node;

                    if ($limit && $limit <= count($children)) {
                        break;
                    }
                }
            }
        }

        return $children;
    }

    /**
     * Appends a new child to the end of this element or inserts a new child after
     * a specified existing child element
     *
     * @param mixed $_source Either instance of element to be appended (in which
     * case it must be cloned), an element in string form, or just the name of a
     * new element to append, or an array of any of the above
     * @param mixed $value OPTIONAL The value of the new element
     * @param mixed $after OPTIONAL If specified, insert the new child immediately
     * after the specified child element. Element, XML string, or element name of
     * the existing child element
     * @return Ampersand_Xml_Element The new child element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function appendChild($source, $value = null, $after = null)
    {
        $child = null;
        $removedChildren = array();
        
        if (!is_null($after)) {
            if (Ampersand_Xml::isValidCompound($after)) {
                $compound = $after;
            } else {
                $compound = $this->getChildCompound($after);
            }

            if ($this->hasChild($compound)) {
                // We have to remove the children in reverse order
                $children = array_reverse($this->getChildren());
                $matchFound = false;
                foreach ($children as $_compound => $_child) {
                    if ($compound === $_compound) {
                        $matchFound = true;
                    } else if (!$matchFound) {
                        array_unshift($removedChildren, $this->removeChild($_compound));
                    }
                }
            }
        }

        if (!is_array($source)) {
            $source = array($source);
        }

        foreach ($source as $_source) {
            $_value = $value;

            if (Ampersand_Xml::isValidName($_source)) {
                $_name = $_source;
                $_namespace = $this->getNamespace("<$_source/>");
            } else {
                if (!is_object($_source)) {
                    $_source = $this->getNewInstance($_source);
                }

                $_name = $_source->getName();
                if (is_null($_value)) {
                    $_value = $_source->hasChildren() ? null : Ampersand_Xml::escape($_source);
                }
                $_namespace = $_source->getNamespace() ? $_source->getNamespace() : Ampersand_XML::NULL_NAMESPACE;
            }
            $child = $this->addChild($_name, $_value, $_namespace);

            $child->extendAttributes($_source, false);

            foreach ($_source->getChildren() as $_sourceChild) {
                $child->appendChild($_sourceChild);
            }
        }

        foreach ($removedChildren as $_child) {
            $this->appendChild($_child);
        }

        return $child;
    }

    /**
     * @todo IMPLEMENT ME
     */
    public function prependChild($source)
    {
        if (!is_object($source)) {
            $source = $this->getNewInstance($source);
        }

        return $this;
    }

    /**
     * Removes a child element from this element
     *
     * @param mixed $target The element to remove; either its compound name (in
     * which case the first matching child is removed), the target in object form,
     * or the target as an XML string in which case the first matching child is
     * removed
     * @return mixed XML string representation of removed child; null if no child
     * was removed. Cannot return removed element object as it becomes inaccessible
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function removeChild($target)
    {
        $matchedChild = null;
        
        if (Ampersand_Xml::isValidCompound($target)) {
            // [if] Remove by compound
            $namespace = $this->getNamespaceFromCompound($target);
            $name = Ampersand_Xml::getNameFromCompound($target, false);
            // If no index is provided we default to 0 to avoid removing multiple nodes
            $index = (int) Ampersand_Xml::getIndexFromCompound($target);

            $matchedChild = $this->children($namespace)->{$name}[$index];
            if (is_object($matchedChild)) {
                $matchedChild = $matchedChild->asValidXml();
            }
            unset($this->children($namespace)->{$name}[$index]);
        } else {
            // [else] Remove by element
            if ($compound = $this->getChildCompound($target)) {
                $matchedChild = $this->removeChild($compound);
            }
        }

        return $matchedChild;
    }

    /**
     * Removes all of the child elements from this element
     *
     * @param string $compound OPTIONAL Target which children to remove using a
     * compound string. If omitted, all children are removed
     * @return Ampersand_Xml_Element 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function removeChildren($compound = null)
    {
        $children = array_reverse($this->getChildren($compound));
        foreach ($children as $_compound => $_child) {
            $this->removeChild($_compound);
        }

        return $this;
    }

    /**
     * Adds an attribute with a unique value to the XML pointed to by this element.
     * Can be used to determine whether this element points a certain piece of XML
     *
     * @param bool $onlyAddIfNotSet OPTIONAL If true and identifier is already set
     * then do not update it
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function addIdentifier($onlyAddIfNotSet = true)
    {
        if (!$onlyAddIfNotSet || !$this->hasAttribute(self::NODE_ID_FIELD)) {
            $this->appendAttribute(self::NODE_ID_FIELD, self::$_nextNodeId++);
        }

        return $this;
    }

    /**
     * Gets this element's unique identifier. Can be used to determine whether
     * this element points to a certain piece of XML
     *
     * @param bool $addIfNotSet OPTIONAL If true and no identifier is currently set then
     * add one using addIdentifier()
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getIdentifier($addIfNotSet = false)
    {
        if (!($id = $this->getAttribute(self::NODE_ID_FIELD)) && $addIfNotSet) {
            $this->addIdentifier();
            $id = $this->getAttribute(self::NODE_ID_FIELD);
        }

        return $id;
    }

    /**
     * Removes this element's unique identifier. Fails gracefully if no identifier
     * is set
     *
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function removeIdentifier()
    {
        return $this->removeAttribute(self::NODE_ID_FIELD);
    }

    /**
     * Merges another XML element into this one, including its children, and their
     * children, etc.
     *
     * @param mixed $source The XML element to extend either as an Ampersand_Xml_Element
     * or an XML string
     * @param mixed $overwrite OPTIONAL Overwrite existing attributes and values
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function extend($source, $overwrite = false)
    {
        if (!is_object($source)) {
            $source = $this->getNewInstance($source);
        }

        if ($source->hasChildren() && ($this->hasChildren() || $overwrite || !$this->getValue())) {
            if (!$this->hasChildren()) {
                $this->unsetValue();
            }

            foreach ($source->getChildren() as $_compound => $_node) {
                $_index = Ampersand_Xml::getIndexFromCompound($_compound);
                if (!$_index && $this->hasChild($_compound)) {
                    $this->getChild($_compound)->extend($_node, $overwrite);
                } else {
                    $this->appendChild($_node);
                }
            }
        } else if (!$this->hasChildren() && (!$this->getValue() || $overwrite)) {
            $this->setValue((string) $source);
        }

        $this->extendAttributes($source, $overwrite);

        return $this;
    }

    /**
     * Appends the children and attributes of another element to this element
     *
     * @param Ampersand_Xml_Element $source The XML element containing the children
     * to be appended, either as an Ampersand_Xml_Element or an XML string
     * @param mixed $after
     * @return Ampersand_Xml_Element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function append($source, $after = null)
    {
        if (!is_object($source)) {
            $source = $this->getNewInstance($source);
        }

        if ($source->hasChildren()) {
            if (!$this->hasChildren()) {
                $this->unsetValue();
            }

            $this->appendChild($source->getChildren(), null, $after);
        } else if (!$this->hasChildren() && !$this->getValue()) {
            $this->setValue((string) $source);
        }

        $this->extendAttributes($source, false);

        return $this;
    }

    /**
     * Merges the attributes from another XML element into this element
     *
     * @param mixed $source The XML element to extend either as an Ampersand_Xml_Element
     * or an XML string
     * @param bool $overwrite Overwrite attributes which already exist in this
     * element
     * @return Ampersand_Xml_Element 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function extendAttributes($source, $overwrite = false)
    {
        if (!is_object($source)) {
            $source = $this->getNewInstance($source);
        }

        foreach ($source->getAttributes() as $_name => $_value) {
            if (!$this->hasAttribute($_name) || $overwrite) {
                $_prefix = Ampersand_Xml::getPrefixFromCompound($_name);
                if (!$this->hasPrefix($_prefix)) {
                    $_namespace = $source->getNamespaceByPrefix($_prefix);
                } else {
                    $_namespace = null;
                }
                $this->setAttribute($_name, $_value, $_namespace);
            }
        }

        return $this;
    }

    /**
     * Extracts the prefix part from a compound string and converts it to a namespace
     *
     * @param string $compound
     * @return mixed Namespace as string; null if no prefix is in the compound
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getNamespaceFromCompound($compound)
    {
        $namespace = null;

        $prefix = Ampersand_Xml::getPrefixFromCompound($compound);
        if ($prefix === self::COMPOUND_WILDCARD) {
            $namespace = $prefix;
        } else if (!is_null($prefix)) {
            $namespace = $this->getNamespaceByPrefix($prefix);
        }

        return $namespace;
    }

    /**
     * Returns this element and its children as an array, recursively
     *
     * @param bool $isCanonical OPTIONAL Whether to ignore attributes
     * @return mixed If has children or includes attributes then returns array,
     * otherwise returns the value of this element
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _asArray($isCanonical = true)
    {
        $result = array();
        if (!$isCanonical) {
            // add attributes
            foreach ($this->getAttributes() as $_name => $_value) {
                if ($_value) {
                    $result['@'][$_name] = $_value;
                }
            }
        }

        // add children values
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $_element) {
                $result[$_element->getName()] = $_element->_asArray($isCanonical);
            }
        } else if (empty($result)) {
            // return as string, if nothing was found
            $result = $this->getValue();
        } else {
            // value has zero key element
            $result[0] = $this->getValue();
        }

        return $result;
    }

    public function asPairs()
    {
        // add children values
        if ($this->hasChildren()) {
            $result = new Ampersand_Pairs();
            foreach ($this->getChildren() as $_element) {
                $result->addPair($_element->getName(), $_element->asPairs());
            }
        } else {
            // return as string, if nothing was found
            $result = $this->getValue();
        }

        return $result;
    }

    /**
     * Searches this element for children which match an xpath expression
     *
     * @param string $path An xpath expression
     * @return array An array of matching elements
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function xpath($path)
    {
        if (!$matches = parent::xpath($path)) {
            foreach ($this->getNamespaces(true) as $_prefix => $_namespace) {
                $this->registerXPathNamespace($_prefix, $_namespace);
            }
            if ($nullNamespace = $this->getNamespaceByPrefix(null)) {
                $this->registerXPathNamespace(self::NULL_PREFIX, $nullNamespace);
            }

            $path = $this->_filterXpath($path);
            $matches = parent::xpath($path);
        }

        return $matches;
    }

    /**
     * Filters an xpath expression to get around bugs in the SimpleXMLElement class
     *
     * @param string $path An xpath expression
     * @return string The filtered xpath expression
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _filterXpath($path)
    {
        if ($nullNamespace = $this->getNamespaceByPrefix(null)) {
            $path = $this->_addNullPrefixToXpath($path);
        }
        
        return $path;
    }

    /**
     * Adds "null prefixes" to element names in an xpath expression. This is
     * necessary if the element uses a namespace which has no prefix, otherwise
     * the xpath method does not work
     *
     * @param string $path An xpath expression
     * @return string The filtered xpath expression
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _addNullPrefixToXpath($path)
    {
        $parts = explode('/', $path);
        foreach ($parts as $_key => $_part) {
            if (!$_part || '.' === $_part{0} || '@' == $_part{0}
                || Ampersand_Xml::getPrefixFromCompound($_part)
            ) {
                continue;
            }

            $parts[$_key] = Ampersand_Xml::buildCompound($_part, self::NULL_PREFIX);
        }

        return implode('/', $parts);
    }

    /**
     * Gets a formatted XML string based on this element ignoring any existing
     * whitespace
     *
     * @param int $indentSize OPTIONAL The size of the indentation to use
     * @param int $indentChar OPTIONAL The character to use for indentation
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function asFormattedXml($indentSize = 4, $indentChar = ' ')
    {
        $document = $this->asDomDocument(false);
        $document->formatOutput = true;
        
        $xml = $document->saveXML();

        $standardIndent = '  ';
        $indent = str_repeat($indentChar, $indentSize);
        if ($indent !== $standardIndent) {
            $lines = explode(PHP_EOL, $xml);
            foreach ($lines as $_key => $_line) {
                $_trimmed = ltrim($_line);
                $_level = (strlen($_line) - strlen($_trimmed)) / strlen($standardIndent);
                $lines[$_key] = str_repeat($indent, $_level) . $_trimmed;
            }
            $xml = implode(PHP_EOL, $lines);
        }

        return $xml;
    }

    public function getInnerXml($indentSize = 4, $indentChar = ' ')
    {
        if ($this->hasChildren()) {
            $childXmlStrings = array();
            foreach ($this->getChildren() as $_child) {
                 $_childXmlString = $_child->asFormattedXml($indentSize, $indentChar);
                 $childXmlStrings[] = Ampersand_Xml::removeDeclaration($_childXmlString);
            }

            return implode(PHP_EOL, $childXmlStrings);
        }

        return $this->getValue();
    }

    public function trimValue()
    {
        if ($this->hasChildren() || stripos($this->asXml(), '<![cdata[')) {
            return $this;
        }

        $value = $this->getValue();

        $value = preg_replace('/^\s*/', '', $value);
        $value = preg_replace('/\s*$/', '', $value);

        $this->setValue($value);

        return $this;
    }

    /**
     * Returns VALID string representation of this element. XML string returned
     * by SimpleXMLElement::asXml often does not include namespace declarations
     * etc., and therefore such XML cannot always be used to create a new XML
     * element object. Unlike asXml, this method should always return valid XML
     *
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function asValidXml($xml = null)
    {
        if (is_null($xml)) {
            $xml = $this->asXml();
        }

        return $this->addNamespacesToXml($xml);
    }

    /**
     * Adds all XML namespace definitions which this object knows about to the root
     * node of an XML string
     *
     * @param string $xml XML string which may be missing namespace definitions
     * @return string XML string with all of this element's namespaces defined
     * in the root node exactly once
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function addNamespacesToXml($xml)
    {
        $namespaces = $this->getNamespaces(true);
        foreach ($namespaces as $_prefix => $_namespace) {
            if (!$_prefix) {
                unset($namespaces[$_prefix]);
            }
        }
        $prefixes = array_flip($namespaces);

        $attributes = Ampersand_Xml::getAttributes($xml, 'xmlns');

        $matchedPrefixes = array();
        if ($attributes) {
            foreach ($attributes as $_name => $_value) {
                if ($_namespacePrefix = Ampersand_Xml::getNameFromCompound($_name, false)) {
                    $matchedPrefixes[] = $_namespacePrefix;
                }
            }
        }

        /**
         * @var array $missing Prefixes used by this element or its children
         * which are not defined in the XML string
         */
        if ($missing = array_diff($prefixes, $matchedPrefixes)) {
            foreach ($missing as $_key => $_prefix) {
                $missing[$_key] = "xmlns:$_prefix=\"{$this->getNamespaceByPrefix($_prefix)}\"";
            }
            $newPrefixes = implode(' ', $missing);
            $xml = preg_replace("#^(\s*(?:\<\?xml[^>]+\?>)?\s*<{$this->getName()})#", "$1 $newPrefixes", $xml);
        }

        return $xml;
    }

    /**
     * Gets a DOMDocument instance based on this element
     *
     * @param bool $preserveWhiteSpace OPTIONAL Whether or not to preserve
     * any irrelevant whitespace which appears within this element
     * @return DOMDocument
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function asDomDocument($preserveWhiteSpace = false)
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = $preserveWhiteSpace;
        @$document->loadXML($this->asValidXml());
        if (!$document->encoding) {
            $document->encoding = 'UTF-8';
        }

        return $document;
    }

    /**
     * Creates and returns a new instance of the current class based on the
     * provided XML element
     *
     * @param mixed $source The XML element to base the new object on, either as
     * an XML string, an XML object or the path to a file containing an XML string
     * @param bool $isFilePath Is $source a file path
     * @return Ampersand_Xml_Element 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function getNewInstance($source, $isFilePath = false)
    {
        $class = get_class($this);

        if (is_object($source)) {
            $source = $source->asValidXml();
        }

        return Ampersand_Xml::factory($source, $isFilePath, $class);
    }

    public function copy($name = null)
    {
        $xml = $this->asFormattedXml();

        if ($name) {
            $xml = preg_replace("#^(\s*(?:\<\?xml[^>]+\?>)?\s*<){$this->getName()}#", "$1{$name}", $xml);
            $xml = preg_replace("#</{$this->getName()}(\s*>\s*)$#", "</{$name}$1", $xml);
        }

        return $this->getNewInstance($xml);
    }
}