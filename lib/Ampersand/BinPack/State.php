<?php
class Ampersand_BinPack_State
{
    /** @var array */
    protected $_dimensions = array();
    /** @var array */
    protected $_items = array();
    /** @var array */
    protected $_unassignedItems = array();
    /** @var array */
    protected $_bins = array();
    /** @var array */
    protected $_openBins = array();
    
    /**
     * @param Ampersand_BinPack_Input $input 
     * @return Ampersand_BinPack_State
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function factory(Ampersand_BinPack_Input $input)
    {
        $state = new Ampersand_BinPack_State();
        
        $state->_dimensions = $input->getDimensions();
        $state->_items = $input->getItems();
        $state->_unassignedItems = $state->_items;
        
        return $state;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getDimensions()
    {
        return $this->_dimensions;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getItems()
    {
        return $this->_items;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getUnassignedItems()
    {
        foreach ($this->_unassignedItems as $_key => $_item) {
            if ($_item->getBin()) {
                unset($this->_unassignedItems[$_key]);
            }
        }
        
        return $this->_unassignedItems;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getBins()
    {
        return $this->_bins;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getOpenBins()
    {
        foreach ($this->_openBins as $_key => $_bin) {
            if ($_bin->getIsClosed()) {
                unset($this->_openBins[$_key]);
            }
        }
        
        return $this->_openBins;
    }
    
    /**
     * @return Ampersand_BinPack_Bin
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com> 
     */
    public function getNewBin()
    {
        return ($this->_bins[] = $this->_openBins[] = Ampersand_BinPack_Bin::factory($this->_dimensions));
    }
}