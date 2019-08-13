<?php
class Ampersand_BinPack_Input
{
    /** @var array */
    protected $_dimensions = array();
    /** @var array */
    protected $_items = array();
    
    /**
     * @param string|Ampersand_BinPack_Dimension_DimensionInterface $identifier
     * @param int|float $binVolume OPTIONAL
     * @return Ampersand_BinPack_Input 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addDimension($identifier, $binVolume = null)
    {
        if ($identifier instanceof Ampersand_BinPack_Dimension_DimensionInterface) {
            $dimension = $identifier;
            $identifier = $dimension->getIdentifier();
        }
        
        if (array_key_exists($identifier, $this->_dimensions)) {
            throw new Exception("'$identifier' is not a unique dimension identifier.");
        }
        
        if (!isset($dimension)) {
            $dimension = Ampersand_BinPack_Dimension_Standard::factory($identifier, $binVolume);
        }
        
        $this->_dimensions[$identifier] = $dimension;
        
        return $this;
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
     * @param string $identifier
     * @param array|int|float $volumes 
     * @param int $quantity OPTIONAL
     * @return Ampersand_BinPack_Input 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addItem($identifier, $volumes, $quantity = 1)
    {
        for ($_i = 1; $_i <= $quantity; $_i++) {
            $this->_items[] = Ampersand_BinPack_Item::factory($identifier, $this->_dimensions, $volumes);
        }
        
        return $this;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getItems()
    {
        return $this->_items;
    }
}