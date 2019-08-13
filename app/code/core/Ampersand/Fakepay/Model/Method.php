<?php
class Ampersand_Fakepay_Model_Method extends Mage_Payment_Model_Method_Cc
{
    protected $_code = 'ampersand_fakepay';
    
    protected $_isGateway               = true;
    protected $_canOrder                = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
}