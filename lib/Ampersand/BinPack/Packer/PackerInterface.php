<?php
interface Ampersand_BinPack_Packer_PackerInterface
{
    /**
     * @param Ampersand_BinPack_State $state
     * @return Ampersand_BinPack_Packer_PackerInterface
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function pack(Ampersand_BinPack_State $state);
}