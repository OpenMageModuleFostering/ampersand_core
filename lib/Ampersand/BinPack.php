<?php
class Ampersand_BinPack
{
    /** @var null|Ampersand_BinPack_Input */
    protected $_input;
    /** @var null|Ampersand_BinPack_Packer_PackerInterface */
    protected $_packer;
    
    /**
     * @return Ampersand_BinPack_Input
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com> 
     */
    public function getInput()
    {
        if (is_null($this->_input)) {
            $this->_input = new Ampersand_BinPack_Input();
        }
        
        return $this->_input;
    }
    
    /**
     * @param string|Ampersand_BinPack_Packer_PackerInterface $packer
     * @return Ampersand_BinPack 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com> 
     */
    public function setPacker($packer)
    {
        if (is_string($packer)) {
            $packerClass = 'Ampersand_BinPack_Packer_' . ucfirst($packer);
            
            if (!class_exists($packerClass)) {
                throw new Exception("Unknown packer '$packer'.");
            }
            
            $packer = new $packerClass;
        }
        
        if (!$packer instanceof Ampersand_BinPack_Packer_PackerInterface) {
            throw new Exception('Packer must be an instance of Ampersand_BinPack_Packer_PackerInterface.');
        }
        
        $this->_packer = $packer;
        
        return $this;
    }
    
    /**
     * @param string|Ampersand_BinPack_Dimension_DimensionInterface $identifier
     * @param int|float $binVolume OPTIONAL
     * @return Ampersand_BinPack 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addDimension($identifier, $binVolume = null)
    {
        $this->getInput()->addDimension($identifier, $binVolume);
        
        return $this;
    }
    
    /**
     * @param string $identifier
     * @param array|int|float $volumes 
     * @param int $quantity OPTIONAL
     * @return Ampersand_BinPack 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function addItem($identifier, $volumes, $quantity = 1)
    {
        $this->getInput()->addItem($identifier, $volumes, $quantity);
        
        return $this;
    }
    
    /**
     * @return Ampersand_BinPack_Output 
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function pack()
    {
        if (is_null($this->_packer)) {
            $this->_packer = new Ampersand_BinPack_Packer_JoshBestFit();
        }
        
        $state = Ampersand_BinPack_State::factory($this->getInput());
        
        $this->_packer->pack($state);
        
        return Ampersand_BinPack_Output::factory($state);
    }
}