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
class Ampersand_Xml_Config extends Ampersand_Xml_Element
{
    public function extend($source, $overwrite = true, array $compoundsToAppend = array())
    {
        if (!is_object($source)) {
            $source = $this->getNewInstance($source);
        }

        foreach ($compoundsToAppend as $_compound) {
            $this->appendChild($source->getChildren($_compound));
            $source->removeChildren($_compound);
        }

        return parent::extend($source, $overwrite);
    }
}