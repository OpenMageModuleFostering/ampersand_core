<?php
class Ampersand_Core_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup
{
    /**
     * Retrieve the name of the upgrade file being run and log for debugging purposes.
     *
     * @return Mage_Core_Model_Resource_Setup
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function startSetup()
    {
        parent::startSetup();
        
        $trace = Ampersand::trace(false);
        $lines = explode(PHP_EOL, $trace);
        $setupLine = $lines[1];
        preg_match('/(?P<filename>\/app\/code\/(.*)):/', $setupLine, $matches);
        
        if (array_key_exists('filename', $matches)) {
            $this->_logSetupStart($matches['filename']);
        } else {
            $this->_logSetupStart($setupLine);
        }
        
        return $this;
    }
    
    /**
     * Log the install script location in system.log so we see any associated errors in place.
     *
     * @param string $message 
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _logSetupStart($message)
    {
        Mage::log('SETUP SCRIPT: ' . $message, null, null, true);
    }
}