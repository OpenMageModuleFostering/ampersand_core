<?php
class Ampersand_BinPack_Bin
{
    /** @var array */
    protected $_binDimensions = array();
    /** @var array */
    protected $_items = array();
    /** @var bool */
    protected $_isClosed = false;
    
    /**
     * @param array $dimensions
     * @return Ampersand_BinPack_Bin
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public static function factory(array $dimensions)
    {
        $bin = new Ampersand_BinPack_Bin();
        
        foreach ($dimensions as $_dimension) {
            $_binDimension = Ampersand_BinPack_BinDimension::factory($bin, $_dimension);
            $bin->_binDimensions[$_dimension->getIdentifier()] = $_binDimension;
        }
        
        return $bin;
    }
    
    /**
     * @param Ampersand_BinPack_Item $item
     * @return Ampersand_BinPack_Bin 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addItem(Ampersand_BinPack_Item $item)
    {
        $this->_items[] = $item;
        $item->setBin($this);
            
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
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getItemIdentifiers()
    {
        $identifiers = array();

        foreach ($this->_items as $_item) {
            $identifiers[] = $_item->getIdentifier();
        }
        
        return $identifiers;
    }
    
    /**
     * @return array
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getItemQuantities()
    {
        $itemQuantities = array();
        
        foreach ($this->_items as $_item) {
            if (!array_key_exists($_item->getIdentifier(), $itemQuantities)) {
                $itemQuantities[$_item->getIdentifier()] = 1;
            } else {
                $itemQuantities[$_item->getIdentifier()] += 1;
            }
        }

        return $itemQuantities;
    }
    
    /**
     * @param Ampersand_BinPack_Item $item 
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getCanFitItem(Ampersand_BinPack_Item $item)
    {
        foreach ($this->_binDimensions as $_binDimension) {
            /* @var $_binDimension Ampersand_BinPack_BinDimension */
            if (!$_binDimension->getCanFitItem($item)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @param Ampersand_BinPack_Item $item 
     * @return float 1 => perfect fit, 0 => least perfect fit, < 0 => item cannot fit
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getQualityOfFit(Ampersand_BinPack_Item $item)
    {
        $quality = 1.0;
        
        foreach ($this->_binDimensions as $_binDimension) {
            /* @var $_binDimension Ampersand_BinPack_BinDimension */
            if (!$_binDimension->getCanFitItem($item)) {
                return -1.0;
            }
            
            $quality *= $_binDimension->getQualityOfFit($item);
        }
        
        return $quality;
    }
    
    /**
     * @param Ampersand_BinPack_Dimension_DimensionInterface $dimension
     * @return null|Ampersand_BinPack_BinDimension
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getBinDimension(Ampersand_BinPack_Dimension_DimensionInterface $dimension)
    {
        if (!array_key_exists($dimension->getIdentifier(), $this->_binDimensions)) {
            return null;
        }
        
        return $this->_binDimensions[$dimension->getIdentifier()];
    }
    
    /**
     * @return bool
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getIsClosed()
    {
        return $this->_isClosed;
    }
    
    /**
     * @return Ampersand_BinPack_Bin 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function close()
    {
        $this->_isClosed = true;
        
        return $this;
    }
    
    /**
     * @return array 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function getStats()
    {
        $stats = array(
            'nr_of_items'                   => count($this->_items),
            'volume_used_pct_normalised'    => 0,
            'dimensions'                    => array(),
        );
        
        foreach ($this->_binDimensions as $_binDimension) {
            $_dimensionIdentifier = $_binDimension->getDimension()->getIdentifier();
            $_stats = $_binDimension->getStats();
            $stats['dimensions'][$_dimensionIdentifier] = $_stats;
            $stats['volume_used_pct_normalised'] += $_stats['volume_used_pct'];
        }
        
        $stats['volume_used_pct_normalised'] /= count($this->_binDimensions);
        
        return $stats;
    }
}