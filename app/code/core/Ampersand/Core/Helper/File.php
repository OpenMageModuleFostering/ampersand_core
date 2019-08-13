<?php
/**
 * method related to file system
 */
class Ampersand_Core_Helper_File extends Mage_Core_Helper_Abstract
{
    /**
     * delete a directory and its contents recursively(child first)
     * @param String $dir File system's directory path 
     */
    public function removeDirectory($dir)
    {
        if (is_null($dir) || !opendir($dir)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($dir),
                        RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            if ($path->isDir()) {
                rmdir($path->__toString());
            } else {
                unlink($path->__toString());
            }
        }

        rmdir($dir);
    }
}

