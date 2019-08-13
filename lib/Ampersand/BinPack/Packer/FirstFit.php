<?php
class Ampersand_BinPack_Packer_FirstFit extends Ampersand_BinPack_Packer_PackerAbstract
{
    public function pack(Ampersand_BinPack_State $state)
    {
        $unassignedItems = $state->getUnassignedItems();
        
        foreach ($unassignedItems as $_item) {
            /* @var $_item Ampersand_BinPack_Item */
            $_selectedBin = null;
            
            foreach ($state->getOpenBins() as $__bin) {
                /* @var $__bin Ampersand_BinPack_Bin */
                if ($__bin->getCanFitItem($_item)) {
                    $_selectedBin = $__bin;
                }
            }
            
            if (!$_selectedBin) {
                $_selectedBin = $state->getNewBin();
            }
            
            $_selectedBin->addItem($_item);
        }
        
        return $this;
    }
}