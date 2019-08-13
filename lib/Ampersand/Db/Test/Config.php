<?php
class Ampersand_Db_Test_Config
{
    /** @var string */
    protected static $_filePath;
    
    /** @var bool */
    protected static $_isCaptureEnabled = false;
    
    /** @var bool */
    protected static $_isFetchEnabled = false;
    
    /** @var array */
    protected static $_capturedOutputs = array();
    
    /** @var array */
    protected static $_fetchedOutputs;
    
    /** @var int */
    protected static $_nrOfFetchedOutputs;
    
    /**
     * Sets the file path which PDO outputs should be captured to and fetched from
     *
     * @param string $filePath 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function setFilePath($filePath)
    {
        self::$_filePath = $filePath;
    }
    
    /**
     * Gets the file path which PDO outputs will be captured to and fetched from
     *
     * @return string 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function getFilePath()
    {
        return self::$_filePath;
    }

    /**
     * Enables capturing of PDO outputs
     * 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function enableCapture()
    {
        self::$_isCaptureEnabled = true;
        self::disableFetch();
    }
    
    /**
     * Disables capturing of PDO outputs
     * 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function disableCapture()
    {
        self::$_isCaptureEnabled = false;
    }
    
    /**
     * Returns whether or not capturing of PDO outputs is currently enabled
     *
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function getIsCaptureEnabled()
    {
        return self::$_isCaptureEnabled;
    }
    
    /**
     * Saves to disk any PDO outputs which have been captured
     *
     * @param string $filePath OPTIONAL File path to save captured outputs to - defaults to
     * Ampersand_Db_Test_Config::getFilePath
     * @return void
     * @throws Ampersand_Db_Test_Exception Thrown if no file path has been specified
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function saveCaptured($filePath = null)
    {
        if (!count(self::$_capturedOutputs)) {
            return;
        }
        
        $filePath = self::_prepareFilePath($filePath);
        
        $dirPath = dirname($filePath);
        
        if ((!@file_exists($dirPath) && !mkdir($dirPath, 0777, true))
                || !$fileHandle = fopen($filePath, 'w')) {
            throw new Ampersand_Db_Test_Exception('Specified file path is not writable.');
        }
        
        $serialized = serialize(self::$_capturedOutputs);
        fwrite($fileHandle, $serialized);
        
        self::$_capturedOutputs = null;
    }
    
    /**
     * Disables capturing of PDO outputs and saves to disk any outputs which have been captured
     *
     * @param string $filePath OPTIONAL File path to save captured outputs to - defaults to
     * Ampersand_Db_Test_Config::getFilePath
     * @throws Ampersand_Db_Test_Exception Thrown if no file path has been specified
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function disableCaptureAndSave($filePath = null)
    {
        $filePath = self::_prepareFilePath($filePath);
        
        self::disableCapture();
        self::saveCaptured($filePath);
    }
    
    /**
     * Adds a value to the array of captured PDO outputs
     *
     * @param string $method
     * @param mixed $output 
     * @param string $hash OPTIONAL
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function addCapturedOutput($method, $output, $hash = null)
    {
        self::$_capturedOutputs[] = array(
            'method'    => $method,
            'output'    => $output,
            'hash'      => $hash,
        );
    }
    
    /**
     * Enables fetching of previously captured and saved PDO outputs instead of calling actual PDO
     * methods 
     *
     * @param string $filePath OPTIONAL File path to fetch captured outputs from - defaults to
     * Ampersand_Db_Test_Config::getFilePath
     * @throws Ampersand_Db_Test_Exception Thrown if no file path has been specified
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function enableFetch($filePath = null)
    {
        self::_fetchOutputs($filePath);
        
        self::$_isFetchEnabled = true;
        self::disableCapture();
    }
    
    /**
     * Disables fetching of PDO outputs - PDO methods will be called instead and database queries
     * will be made where appropriate
     * 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function disableFetch()
    {
        self::$_isFetchEnabled = false;
    }
    
    /**
     * Returns whether or not fetching of PDO outputs is currently enabled
     *
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function getIsFetchEnabled()
    {
        return self::$_isFetchEnabled;
    }
    
    /**
     * Fetches the next previously captured PDO output from the data source
     *
     * @param string $method OPTIONAL
     * @return mixed
     * @throws Ampersand_Db_Test_Exception Thrown if no file path has been specified
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public static function fetchNextOutput($method = null, $hash = null)
    {
        if (is_null(self::$_fetchedOutputs)) {
            self::_fetchOutputs();
        }
        
        $outputData = array_shift(self::$_fetchedOutputs);
        
        if (!is_null($method) && $method !== $outputData['method']) {
            throw new Ampersand_Db_Test_Exception(
                'Method mismatch when fetching captured PDO output at index '
                . self::$_nrOfFetchedOutputs . '. Next output in queue is for method '
                . "'{$outputData['method']}' but call was made to method '$method'."
            );
        }
        if ($hash !== $outputData['hash']) {
            throw new Ampersand_Db_Test_Exception(
                'Hash mismatch when fetching captured PDO output at index '
                . self::$_nrOfFetchedOutputs . '. Next output in queue has hash '
                . "'{$outputData['hash']}' but the call that was made had hash '$hash'."
            );
        }
        
        self::$_nrOfFetchedOutputs++;
        
        return $outputData['output'];
    }
    
    /**
     * Fetches all of the captured PDO outputs from the data source so that they can subsequently
     * be fetched one at a time
     *
     * @param string $filePath OPTIONAL File path to fetch captured outputs from - defaults to
     * Ampersand_Db_Test_Config::getFilePath
     * @throws Ampersand_Db_Test_Exception Thrown if no file path has been specified
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected static function _fetchOutputs($filePath = null)
    {
        $filePath = self::_prepareFilePath($filePath);
        
        if (!is_readable($filePath)) {
            throw new Ampersand_Db_Test_Exception('Specified file path is not readable.');
        }
        
        $serialized = file_get_contents($filePath);
        self::$_fetchedOutputs = unserialize($serialized);
        self::$_nrOfFetchedOutputs = 0;
    }
    
    /**
     * Returns the passed file path if it is set, otherwise returns
     * Ampersand_Db_Test_Config::getFilePath
     *
     * @param string $filePath
     * @return string
     * @throws Ampersand_Db_Test_Exception Thrown if no file path has been specified
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected static function _prepareFilePath($filePath)
    {
        if (!strlen($filePath)) {
            $filePath = self::getFilePath();
        }
        
        if (!strlen($filePath)) {
            throw new Ampersand_Db_Test_Exception('No file path specified.');
        }
        
        return $filePath;
    }
}