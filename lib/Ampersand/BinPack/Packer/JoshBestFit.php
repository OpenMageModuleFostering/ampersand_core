<?php
class Ampersand_BinPack_Packer_JoshBestFit extends Ampersand_BinPack_Packer_PackerAbstract
{
    public function pack(Ampersand_BinPack_State $state)
    {
        $bin = $state->getNewBin();
        foreach ($state->getUnassignedItems() as $_item) {
            if (!$bin->getCanFitItem($_item)) {
                $bin->addItem($_item);
                $bin->close();
                $bin = $state->getNewBin();
            }
        }
        
        $_prevItems = null;
        while ($_items = $state->getUnassignedItems()) {
            if ($_prevItems && count($_items) == count($_prevItems)) {
                $state->getNewBin();
            }
            
            foreach ($state->getOpenBins() as $__bin) {
                $__bestFittingItem = null;
                
                $__qualityOfBestFit = null;
                foreach ($_items as $___item) {
                    if ($___item->getBin() || !$__bin->getCanFitItem($___item)) {
                        continue;
                    }
                    
                    $___fitQuality = $__bin->getQualityOfFit($___item);
                    if (is_null($__bestFittingItem) || $___fitQuality > $__qualityOfBestFit) {
                        $__bestFittingItem = $___item;
                        $__qualityOfBestFit = $___fitQuality;
                    }
                }
                
                if ($__bestFittingItem) {
                    $__bin->addItem($__bestFittingItem);
                } else {
                    $__bin->close();
                }
            }
            
            $_prevItems = $_items;
        }
        
        return $this;
    }
}