<?php
/**
 * Magento 1.4.1.1 is missing Zend_Filter_Compress, so we include the 
 * Zend_Filter_Compress file and folder from Magento 1.6.0.0.
 */
if (!class_exists('Zend_Filter_Compress')) {
    set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
}
class Ampersand_Filter_Compress extends Zend_Filter_Compress
{
    /**
     * Returns the current adapter, instantiating it if necessary
     * 
     * Ampersand bugfix relating to errors:
     * - Warning: include(Zip.php): failed to open stream: No such file or directory
     * - Warning: include(): Failed opening 'Zip.php' for inclusion
     *
     * @return string
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function getAdapter()
    {
        if ($this->_adapter instanceof Zend_Filter_Compress_CompressInterface) {
            return $this->_adapter;
        }

        $adapter = $this->_adapter;
        $options = $this->getAdapterOptions();
        if (!class_exists($adapter, false)) { // autoload 'false' added by Ampersand
            #require_once 'Zend/Loader.php';
            if (Zend_Loader::isReadable('Zend/Filter/Compress/' . ucfirst($adapter) . '.php')) {
                $adapter = 'Zend_Filter_Compress_' . ucfirst($adapter);
            }
            Zend_Loader::loadClass($adapter);
        }

        $this->_adapter = new $adapter($options);
        if (!$this->_adapter instanceof Zend_Filter_Compress_CompressInterface) {
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("Compression adapter '" . $adapter . "' does not implement Zend_Filter_Compress_CompressInterface");
        }
        return $this->_adapter;
    }
}