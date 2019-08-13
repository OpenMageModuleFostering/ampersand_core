<?php
abstract class Ampersand_Db_Test_Abstract
{
    /** @var PDO|PDOStatement */
    protected $_pdoInstance;
    
    /**
     * Proxy any method calls to the PDO|PDOStatement instance associated to this object. Captures
     * the output of any calls to the PDO|PDOStatement instance
     * 
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function __call($name, array $arguments)
    {
        $output = call_user_func_array(array($this->_pdoInstance, $name), $arguments);
        
        if (Ampersand_Db_Test_Config::getIsCaptureEnabled()) {
            $this->_captureMethodOutput($name, $arguments, $output);
        } else if (Ampersand_Db_Test_Config::getIsFetchEnabled()) {
            $output = $this->_fetchNextOutput($name, $arguments);
        } else {
            return $output;
        }
        
        return $this->_prepareReturnValue($output);
    }
    
    /**
     * Persists a method output value so that it can be fetched later
     *
     * @param mixed $output 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _captureMethodOutput($method, array $arguments, $output)
    {
        if ($output === $this) {
            $output = new Ampersand_Db_Test_Output_This();
        }
        if ($output instanceof PDOStatement) {
            $output = new Ampersand_Db_Test_Output_Statement();
        }
        
        $hash = $this->_hashMethodCall($method, $arguments);
        
        Ampersand_Db_Test_Config::addCapturedOutput($method, $output, $hash);
    }
    
    /**
     * Fetches the next output value from the data source
     *
     * @param string $method
     * @param mixed $output 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _fetchNextOutput($method, array $arguments)
    {
        $hash = $this->_hashMethodCall($method, $arguments);
        
        return Ampersand_Db_Test_Config::fetchNextOutput($method, $hash);
    }
    
    /**
     * Produces a hash string based on a method and the arguments passed to it. When fetching
     * captured PDO outputs from the data source this hash can be used to verify that the correct
     * output is being returned
     *
     * @param string $method
     * @param array $arguments
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _hashMethodCall($method, array $arguments)
    {
        return null;
    }
    
    /**
     * Prepares PDO method output for return to caller. Creates wrapper object where necessary
     *
     * @param type $returnValue
     * @return mixed 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    protected function _prepareReturnValue($returnValue)
    {
        if ($returnValue instanceof PDOStatement) {
            $returnValue = new Ampersand_Db_Test_Statement($returnValue);
        } else if ($returnValue instanceof Ampersand_Db_Test_Output_This) {
            $returnValue = $this;
        } else if ($returnValue instanceof Ampersand_Db_Test_Output_Statement) {
            $returnValue = new Ampersand_Db_Test_Statement();
        }
        
        return $returnValue;
    }
}