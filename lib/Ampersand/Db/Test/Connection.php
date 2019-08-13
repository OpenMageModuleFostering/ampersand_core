<?php
class Ampersand_Db_Test_Connection extends Ampersand_Db_Test_Abstract
{
    /**
     * Creates a PDO object so that we have a database connection. Calls to $this will then be
     * proxied to that PDO object
     * 
     * @param string $dsn Data source name
     * @param string $username
     * @param string $password OPTIONAL
     * @param array $driverOptions OPTIONAL
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function __construct($dsn, $username, $password = null, array $driverOptions = array())
    {
        $this->_pdoInstance = new PDO($dsn, $username, $password, $driverOptions);
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
        switch ($method) {
            case 'exec':
            case 'prepare':
            case 'query':
            case 'quote':
                $subject = $arguments[0];
                break;
            
            default:
                return null;
        }
        
        return md5($subject);
    }
}