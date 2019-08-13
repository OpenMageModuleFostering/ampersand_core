<?php
interface Ampersand_BinPack_Dimension_DimensionInterface
{
    /**
     * @return string
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getIdentifier();
    
    /**
     * @return int|float
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getBinVolume();
    
    /**
     * @param Ampersand_BinPack_ItemDimension $itemDimension
     * @return Ampersand_BinPack_Dimension_DimensionInterface
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addItemDimension(Ampersand_BinPack_ItemDimension $itemDimension);
    
    /**
     * @param Ampersand_BinPack_BinDimension $binDimension
     * @return Ampersand_BinPack_Dimension_DimensionInterface
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addBinDimension(Ampersand_BinPack_BinDimension $binDimension);
}