<?php
class Ampersand_BinPack_BinDimension
{
    /** @var Ampersand_BinPack_Bin */
    protected $_bin;
    /** @var Ampersand_BinPack_Dimension_DimensionInterface */
    protected $_dimension;
    
    /**
     * @param Ampersand_BinPack_Bin $bin
     * @param Ampersand_BinPack_Dimension_DimensionInterface $dimension
     * @return Ampersand_BinPack_BinDimension 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function factory(Ampersand_BinPack_Bin $bin,
        Ampersand_BinPack_Dimension_DimensionInterface $dimension
    ) {
        $binDimension = new Ampersand_BinPack_BinDimension();
        
        $binDimension->_bin = $bin;
        $binDimension->_dimension = $dimension;
        
        $dimension->addBinDimension($binDimension);
        
        return $binDimension;
    }
    
    /**
     * @return int|float
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getVolumeRemaining()
    {
        return ($this->_dimension->getBinVolume() - $this->getVolumeUsed());
    }
    
    /**
     * @return int|float
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getVolumeUsed()
    {
        $volumeUsed = 0;
        
        foreach ($this->_bin->getItems() as $_item) {
            $volumeUsed += $_item->getItemDimension($this->_dimension)->getVolume();
        }
        
        return $volumeUsed;
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
     * @return Ampersand_BinPack_Dimension
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getDimension()
    {
        return $this->_dimension;
    }
    
    /**
     * @param Ampersand_BinPack_Item $item
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getCanFitItem(Ampersand_BinPack_Item $item)
    {
        if (!$volumeRemainingInBin = $this->getVolumeRemaining()) {
            return false;
        }
        
        $itemVolume = $item->getItemDimension($this->_dimension)->getVolume();
        
        return ($itemVolume <= $volumeRemainingInBin);
    }
    /**
     * @param Ampersand_BinPack_Item $item 
     * @return float 1 => perfect fit, 0 => least perfect fit, < 0 => item cannot fit
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getQualityOfFit(Ampersand_BinPack_Item $item)
    {
        $volumeRemainingInBin = $this->getVolumeRemaining();
        $itemVolume = $item->getItemDimension($this->_dimension)->getVolume();
        
        if ($itemVolume > $volumeRemainingInBin) {
            return -1.0;
        }
        
        return (1.0 - (($volumeRemainingInBin - $itemVolume) / $this->_dimension->getBinVolume()));
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getStats()
    {
        $binVolume = $this->_dimension->getBinVolume();
        $volumeUsed = $this->getVolumeUsed();
        
        return array(
            'bin_volume'        => $binVolume,
            'volume_used'       => $volumeUsed,
            'volume_remaining'  => $this->getVolumeRemaining(),
            'volume_used_pct'   => $volumeUsed / $binVolume,
        );
    }
}