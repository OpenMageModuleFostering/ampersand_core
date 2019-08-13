<?php
class Ampersand_BinPack_Output
{
    protected $_bins;
    
    /**
     * @param Ampersand_BinPack_State $state 
     * @return Ampersand_BinPack_Output
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function factory(Ampersand_BinPack_State $state)
    {
        $output = new Ampersand_BinPack_Output();
        
        $output->_setBins($state->getBins());
        
        return $output;
    }
    
    /**
     * @param array $bins 
     * @return void
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    protected function _setBins(array $bins)
    {
        $this->_bins = array();
        
        foreach ($bins as $_bin) {
            if ($_bin->getItems()) {
                $this->_bins[] = $_bin;
            }
        }
    }
    
    /**
     * @return int
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getNrOfBins()
    {
        return count($this->_bins);
    }
    
    /**
     * @param bool $withQuantities OPTIONAL
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getAssignments($withQuantities = false)
    {
        $assignments = array();
        
        $methodName = $withQuantities ? 'getItemQuantities' : 'getItemIdentifiers';
        foreach ($this->_bins as $_bin) {
            $assignments[] = $_bin->$methodName();
        }
        
        return $assignments;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getAssignmentStats()
    {
        $stats = array(
            'nr_of_bins'                    => count($this->_bins),
            'volume_used_pct_normalised'    => 0,
            'bins'                          => array(),
        );
        
        foreach ($this->_bins as $_bin) {
            $_stats = $_bin->getStats();
            $stats['bins'][] = $_bin->getStats();
            $stats['volume_used_pct_normalised'] += $_stats['volume_used_pct_normalised'];
        }
        
        $stats['volume_used_pct_normalised'] /= count($this->_bins);
        
        return $stats;
    }
}