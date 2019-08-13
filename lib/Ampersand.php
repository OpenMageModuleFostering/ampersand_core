<?php
final class Ampersand
{
    /**
     * Property for storing stopwatch timings
     *
     * @var double $_stopwatchClock
     */
    private static $_stopwatchClock = null;
    
    /**
     * Initialize Magento for scripts.
     *
     * @param string $path 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function initMage($path = '.')
    {
        require_once($path . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php');
        Mage::app();
        umask(0);
    }
    
    /**
     * Retrieve stack trace from current location
     *
     * @param bool $die OPTIONAL Die with trace. False to return trace.
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function trace($die = true)
    {
        $e = new Exception();
        $trace = $e->getTraceAsString();
        
        return $die ? die($trace) : $trace;
    }
    
    /**
     * Toggle error reporting
     *
     * @param bool $enabled OPTIONAL Enable error reporting. False to disable.
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function errorReporting($enabled = true)
    {
        if ($enabled) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Mage::log() with class, method and line number information
     *
     * @param string $message OPTIONAL Log message
     * @param string $filename OPTIONAL Log filename
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function log($message = null, $filename = 'system.log')
    {
        $backtrace = debug_backtrace();
        $className = $backtrace[1]['class'];
        $methodName = $backtrace[1]['function'];
        $lineNumber = $backtrace[0]['line'];
        Mage::log($className . '::' . $methodName . ' (line ' . $lineNumber . ') ' . $message, null, $filename);
    }
    
    /**
     * Return the contents of var_dump rather than direct output.
     *
     * @param mixed $content
     * @return string
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function varDump($content)
    {
        ob_start();
        var_dump($content);

        return ob_get_clean();
    }
    
    /**
     * Return the time elapsed between each call of this method.
     *
     * @param string $message
     * @return string 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public static function stopwatch($message = null, $convertNegligable = true)
    {
        $microtime = microtime(true);
        if (!self::$_stopwatchClock) {
            self::$_stopwatchClock = $microtime;
            return;
        }
        
        $difference = $microtime - self::$_stopwatchClock;
        
        // these numbers are hard to interpret so sometimes best to ignore them
        if ($convertNegligable && strpos($difference, 'E-') !== FALSE) {
            $difference = 'negligable';
        } else {
            $difference .= ' seconds';
        }
        
        self::$_stopwatchClock = $microtime;
        
        return !is_null($message) ? "{$difference} :: {$message}" : null;
    }
    
    /**
     * Reset any Ampersand-related properties
     *
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function reset()
    {
        self::$_stopwatchClock = null;
        
        Ampersand_Registry::reset();
    }
}