<?php
class Ampersand_System_Model_Images
{
    /**
     * Path to the source directory where images to be uploaded are held
     *
     * @var string $_srcDir
     */
    protected $_srcDir;

    /**
     * Path to the destination directory where images are to be copied
     *
     * @var string $_destDir
     */
    protected $_destDir;

    /**
     * Used to reference the system.xml config object
     *
     * @var Mage_Core_Model_Config $_config
     */
    protected $_config;

    /**
     * Used to store the details of the images to be upload
     *
     * @var array $_files
     */
    protected $_files;

     /**
     * List of valid image file extensions
     *
     * @var array $_files
     */
    protected $_allowedFileTypes = array(
        'jpg',
        'jpeg',
        'gif',
        'png',
    );

    /**
     * Initialises the source, destination directories and loads the system.xml config
     *
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    public function  __construct()
    {
        $this->_srcDir = Mage::app()->getConfig()->getBaseDir() . DS .'media' . DS . 'ampersand';
        $this->_destDir = Mage::app()->getConfig()->getBaseDir() . DS .'media';
        $this->_config = Mage::getConfig()->loadModulesConfiguration('system.xml');
    }

    /**
     * Returns the config page for given image file name in a special format i.e. sales-identity-logo.jpg
     * 'sales-identity-logo' would be the config path
     *
     * @param string $path
     * @return string
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _getConfigPath($path)
    {
        $path = split('\.',$path);
        $path = str_replace('-',DS,$path[0]);
        $configPath = $path;
        return $configPath;
    }

    /**
     * Returns the source path under the media/ampersand folder to the image file to be uploaded
     * i.e. 'stores/default'
     *
     * @param string $path
     * @return string
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _getSourcePath($path)
    {
        $sourcePath = str_replace($this->_srcDir . DS,'',$path);
        return $sourcePath;
    }

    /**
     * Returns the destination path under the destination upload url by converting the source
     * i.e. source path - 'stores/default' would be convert to destination path - 'stores/1'
     *
     * @param string $path
     * @return string
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function getDestinationPath($path)
    {
        $path = $this->_getSourcePath($path);
        $path = split(DS,$path);
        if (count($path)>1) {
            if ($path[0]=='websites') {
                $id = Mage::app()->getWebsite($path[1])->getId();
            } elseif ($path[0]=='stores') {
                 $id = Mage::app()->getStore($path[1])->getId();
            }
            $destinationPath = $path[0]. DS . $id;
        } else {
            $destinationPath = $path[0];
        }
        return $destinationPath;
    }

    /**
     * Returns the uploadUrl folder to which images are to be copied
     * This is retrieved by searching the system.xml file using the config path
     *
     * @param string $configPath
     * @return string
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _getUploadUrl($configPath)
    {
        $configPath = $this->_getConfigPath($configPath);
        $configPath = split(DS, $configPath);
        $results = $this->_config->getNode('sections/' . $configPath[0] . '/groups/' . $configPath[1] . '/fields/' . $configPath[2] . '/upload_dir');
        if (empty($results)) throw new Exception();
        return (string) $results;
    }

    /**
     * Returns the scope to which this image should be associated with i.e. default, websites, stores
     *
     * @param string $path
     * @return string
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _getScope($path)
    {
        $path = $this->_getSourcePath($path);
        $path = split(DS,$path);
        return $path[0];
    }

    /**
     * Returns the scopeId to which this image should be associated
     *
     * @param string $path
     * @return int
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _getScopeId($path)
    {
        $path = $this->_getSourcePath($path);
        $path = $this->getDestinationPath($path);
        $path = split(DS,$path);
        if ($path[0]=='websites' || $path[0]=='stores') {
            $scopeId = $path[1];
        } else {
            $scopeId = 0;
        }
        return $scopeId;
    }

    /**
     * Checks if a image file has a valid file extension
     *
     * @param string $filename
     * @return boolean
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _isValidImage($filename)
    {
        $filename = explode('.', $filename);
        if (!in_array($filename[1], $this->_allowedFileTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the image details for all the images in the source folder i.e. 'media/ampersand'
     * This recusively iterates through the folders until it gets to an image file upon which the
     * images details are retrieved and added to an array
     *
     * @param string $outerDir
     * @return array
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _getImageData($outerDir)
    {
        $dirs = array_diff( scandir( $outerDir ), Array( '.', '..', '.svn' ) );
        foreach ( $dirs as $d ) {
            if ( is_dir($outerDir . DS . $d)) {
                $this->_getImageData( $outerDir . DS . $d );
            } else {
                if (!$this->_isValidImage($d)) {
                    throw new Exception();
                }

                $this->_files[] = array(
                    'configPath' => $this->_getConfigPath($d),
                    'sourcePath' => $this->_getSourcePath($outerDir),
                    'filename' => $d,
                    'destinationPath' => $this->getDestinationPath($outerDir),
                    'uploadurl' => $this->_getUploadUrl($d),
                    'scope' => $this->_getScope($outerDir),
                    'scopeId' => $this->_getScopeId($outerDir),
                );
            }
        }
        
        return $this->_files;
    }

    /**
     * Creates a directory path under the destination upload url
     *
     * @param string $path
     * @return boolean
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _createDirectory($path)
    {
        $path = explode(DS, $path, -1);
        $path = implode(DS, $path);
        if (!mkdir($path, 0777, true)) {
           return false;
        }
        return true;
    }

    /**
     * Copies a file from a source path to a destination path
     *
     * @param string $src
     * @param string $dest
     * @return boolean
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _copyFile($src,$dest)
    {
        if (!copy($src, $dest)) {
            return false;
        }
        return true;
    }

    /**
     * Iterates through the array of images checks to see if file already exists, if not constructs the correct destination path,
     * copies the image, and adds the relevane values in 'core_config_data' table
     * 
     * @param array $images
     * @return boolean
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    protected function _uploadImage($images, $allowOverwrite)
    {
        foreach($images as $image){
            $destinationPath = $this->_destDir . DS . $image['uploadurl'] . DS . $image['destinationPath'] . DS . $image['filename'];
            $src = $this->_srcDir . DS . $image['sourcePath'] . DS . $image['filename'];
            if (file_exists($destinationPath) && !$allowOverwrite) {
                continue;
            } else {
                $this->_createDirectory($destinationPath);
                if ($this->_copyFile($src,$destinationPath)) {
                    if ($image['scope']=='default') {
                        $config = Mage::getModel('ampersand_system/config')
                        ->setDefault()
                        ->setNode(array(
                            $image['configPath'] => $image['destinationPath'] . DS . $image['filename'],
                        ))
                        ->save();
                    } else if($image['scope']=='websites') {
                        $config = Mage::getModel('ampersand_system/config')
                        ->setWebsite($image['scopeId'])
                        ->setNode(array(
                            $image['configPath'] => $image['destinationPath'] . DS . $image['filename'],
                        ))
                        ->save();
                    } else if($image['scope']=='stores') {
                        $config = Mage::getModel('ampersand_system/config')
                        ->setStore($image['scopeId'])
                        ->setNode(array(
                            $image['configPath'] => $image['destinationPath'] . DS . $image['filename'],
                        ))
                        ->save();
                    }
                }
            }
        }
        return true;
    }

    /**
     * Calls the functions to retrieve image details of files to be uploaded and the passes these to the upload
     * function
     *
     * @param boolean $allowOverwrite
     * @author Stephen O'Shea <stephen.o'shea@ampersandcommerce.com>
     */
    public function upload($allowOverwrite = false)
    {
        $images = $this->_getImageData( $this->_srcDir );
        $this->_uploadImage($images, $allowOverwrite);
    }
}