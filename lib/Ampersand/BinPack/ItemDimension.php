<?php
class Ampersand_BinPack_ItemDimension
{
    /** @var Ampersand_BinPack_Item */
    protected $_item;
    /** @var Ampersand_BinPack_Dimension_DimensionInterface */
    protected $_dimension;
    /** @var null|int|double */
    protected $_volume;
    
    /**
     * @param Ampersand_BinPack_Item $item
     * @param Ampersand_BinPack_Dimension_DimensionInterface $dimension
     * @param int|double $volume
     * @return Ampersand_BinPack_ItemDimension 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function factory(Ampersand_BinPack_Item $item,
        Ampersand_BinPack_Dimension_DimensionInterface $dimension, $volume
    ) {
        $itemDimension = new Ampersand_BinPack_ItemDimension();
        
        $itemDimension->_item = $item;
        $itemDimension->_dimension = $dimension;
        $itemDimension->_volume = $volume;
        
        $dimension->addItemDimension($itemDimension);
        
        return $itemDimension;
    }
    
    /**
     * @return int|double
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getVolume()
    {
        return $this->_volume;
    }
    
    /**
     * @return Ampersand_BinPack_Item
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getItem()
    {
        return $this->_item;
    }
}