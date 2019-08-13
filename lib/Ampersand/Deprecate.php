<?php
/**
 * Ampersand IT Library
 *
 * @category    Ampersand_Library
 * @package     Ampersand_Deprecate
 * @copyright   Copyright (c) 2008-2011 Ampersand IT (UK) Ltd. (http://www.ampersandit.co.uk)
 * @license     TBC
 */

/**
 * @category    Ampersand_Library
 * @package     Ampersand_Deprecate
 * @author      Josh Di Fabio <josh.difabio@ampersandit.co.uk>
 */
class Ampersand_Deprecate
{
    /* static functionality */
    
    /**
     * Logs that a call was made to a deprecated method. Optionally allows for a custom message to
     * be included in the logged data
     *
     * @param string $message OPTIONAL
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function log($message = null)
    {
        $singleton = self::getSingleton();
        
        $description = $singleton->_getDescription();
        
        $singleton->_mageLog(
            $singleton->_getTextToLog($description, $message)
        );
    }
    
    /**
     * Sets a filename to be used when logging calls to Ampersand_Deprecate::log method
     *
     * @param string $filename 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function setLogFilename($filename)
    {
        self::getSingleton()->_logFilename = $filename;
    }
    
    /**
     * Returns the current singleton instance of this class as stored in the registry
     *
     * @return Ampersand_Deprecate
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function getSingleton()
    {
        if (!Ampersand_Deprecate_Registry::has('singleton')) {
            Ampersand_Deprecate_Registry::set(
                'singleton', new Ampersand_Deprecate
            );
        }
        
        return Ampersand_Deprecate_Registry::get('singleton');
    }
    
    /* non-static functionality */
    
    protected $_logFilename = 'ampersand-deprecate.log';

    /**
     * Passes the provided data structure to Mage::log together with the log filename which
     * Ampersand_Deprecate is configured to use
     *
     * @param mixed $data
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _mageLog($data)
    {
        if (!class_exists('Mage')) {
            return;
        }
        
        Mage::log($data, null, $this->_logFilename);
    }
    
    /**
     * Gets a description of the call which was made to the deprecated method
     *
     * @param int $backtraceLevel OPTIONAL The number of backtrace elements to ignore when working
     * out which class and method is referencing the deprecate class. If _getDescription is called
     * from a public method within Ampersand_Deprecate which is itself called from the deprecated
     * method then this parameter should be 1; if the backtrace contains two method calls between
     * _getDescription and the deprecated method then this parameter should be 2, etc.
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getDescription($backtraceLevel = 1)
    {
        $backtrace = debug_backtrace(false);
        
        if (1 + $backtraceLevel >= count($backtrace)) {
            return null;
        }
        
        return sprintf(
            "%s::%s is deprecated according to %s(%s) but was called by %s(%s)",
            $backtrace[1 + $backtraceLevel]['class'], $backtrace[1 + $backtraceLevel]['function'],
            $backtrace[$backtraceLevel]['file'], $backtrace[$backtraceLevel]['line'],
            $backtrace[1 + $backtraceLevel]['file'], $backtrace[1 + $backtraceLevel]['line']
        );
    }
    
    /**
     * Returns a single string based on a derived description of the call to the deprecated method
     * and an optional custom message provided within the deprecated code
     *
     * @param string $description
     * @param null|string $customMessage
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _getTextToLog($description, $customMessage)
    {
        if (!is_null($customMessage)) {
            $description .= "\nCustom message: '$customMessage'";
        }
        
        $e = new Exception;
        $description .= "\nTrace:\n{$e->getTraceAsString()}";
        
        return $description;
    }
}