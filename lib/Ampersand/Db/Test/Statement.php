<?php
class Ampersand_Db_Test_Statement extends Ampersand_Db_Test_Abstract implements IteratorAggregate
{
    /**
     * Creates a PDOStatement object so that we can make calls to the database. Calls to $this will
     * then be proxied to that PDOStatement object
     * 
     * @param PDOStatement $pdoStatement OPTIONAL Manually provide the PDOStatement instance to 
     * proxy to; necessary when PDO internally generates an instance of PDOStatement 
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     */
    public function __construct(PDOStatement $pdoStatement = null)
    {
        $this->_pdoInstance = $pdoStatement ? $pdoStatement : new PDOStatement();
    }
    
    /**
     * Returns the PDOStatement instance to iterate over. This method is required by
     * IteratorAggregate interface
     *
     * @return PDOStatement
     * @author Josh Di Fabio <josh.difabio@ampersandit.co.uk>
     * 
     * @todo Implement Iterator instead of IteratorAggregate and return capture statement instances
     * instead of PDOStatement instances
     */
    public function getIterator()
    {
        return $this->_pdoInstance;
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
            case 'bindParam':
                $subject = $arguments[0] . ' : ' . (string) $arguments[1];
                break;
            
            default:
                return null;
        }
        
        return md5($subject);
    }
}