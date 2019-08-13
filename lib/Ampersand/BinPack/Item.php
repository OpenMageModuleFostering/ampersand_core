<?php
class Ampersand_BinPack_Item
{
    /** @var mixed */
    protected $_identifier;
    /** @var array */
    protected $_itemDimensions = array();
    /** @var Ampersand_BinPack_Bin */
    protected $_bin;
    
    /**
     * @param string $identifier
     * @param array $dimensions
     * @param array $volumes
     * @return Ampersand_BinPack_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function factory($identifier, array $dimensions, array $volumes)
    {
        $item = new Ampersand_BinPack_Item();
        
        $item->_identifier = $identifier;
        
        foreach ($dimensions as $_dimension) {
            if (!array_key_exists($_dimension->getIdentifier(), $volumes)) {
                continue;
            }
            
            $_volume = $volumes[$_dimension->getIdentifier()];
            $_itemDimension = Ampersand_BinPack_ItemDimension::factory($item, $_dimension, $_volume);
            
            $item->_itemDimensions[$_dimension->getIdentifier()] = $_itemDimension;
        }
        
        return $item;
    }
    
    /**
     * @param Ampersand_BinPack_Dimension_DimensionInterface $dimension
     * @return null|Ampersand_BinPack_ItemDimension
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getItemDimension(Ampersand_BinPack_Dimension_DimensionInterface $dimension)
    {
        if (!array_key_exists($dimension->getIdentifier(), $this->_itemDimensions)) {
            return null;
        }
        
        return $this->_itemDimensions[$dimension->getIdentifier()];
    }
    
    /**
     * @param Ampersand_BinPack_Bin $bin
     * @return Ampersand_BinPack_Item 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function setBin(Ampersand_BinPack_Bin $bin)
    {
        if (!is_null($this->_bin)) {
            throw new Exception('Item is already assigned to a bin.');
        }
        
        $this->_bin = $bin;
        
        return $this;
    }
    
    /**
     * @return Ampersand_BinPack_Bin
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getBin()
    {
        return $this->_bin;
    }
    
    /**
     * @return mixed
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }
}