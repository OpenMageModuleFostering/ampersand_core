<?php
class Ampersand_BinPack_Dimension_Standard implements Ampersand_BinPack_Dimension_DimensionInterface
{
    /** @var string */
    protected $_identifier;
    /** @var int|float */
    protected $_binVolume;
    /** @var array */
    protected $_itemDimensions = array();
    /** @var array */
    protected $_binDimensions = array();
    
    /**
     * @param string $identifier
     * @param int|float $binVolume 
     * @return Ampersand_BinPack_Dimension_Standard
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function factory($identifier, $binVolume)
    {
        $dimension = new Ampersand_BinPack_Dimension_Standard();
        
        $dimension->_identifier = $identifier;
        $dimension->_binVolume = $binVolume;
        
        return $dimension;
    }
    
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }
    
    /**
     * @return int|float
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getBinVolume()
    {
        return $this->_binVolume;
    }
    
    /**
     * @param Ampersand_BinPack_ItemDimension $itemDimension
     * @return Ampersand_BinPack_Dimension_DimensionInterface
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addItemDimension(Ampersand_BinPack_ItemDimension $itemDimension)
    {
        $this->_itemDimensions[] = $itemDimension;
        
        return $this;
    }
    
    /**
     * @param Ampersand_BinPack_BinDimension $binDimension
     * @return Ampersand_BinPack_Dimension_DimensionInterface
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addBinDimension(Ampersand_BinPack_BinDimension $binDimension)
    {
        $this->_binDimensions[] = $binDimension;
        
        return $this;
    }
}