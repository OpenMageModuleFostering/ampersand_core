<?php
class Ampersand_System_Model_Index
{
    /**
     * Perform a full re-index.
     *
     * @return Ampersand_System_Model_Index
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    public function reindexAll()
    {
        $processCollection = $this->_getIndexProcesses();
        foreach ($processCollection as $_process) {
            $_process->reindexEverything();
        }
        
        return $this;
    }
    
    /**
     * Retreive array of all index processes.
     *
     * @return array
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getIndexProcesses()
    {
        $processes = array();
        $collection = $this->_getModel()->getProcessesCollection();
        foreach ($collection as $_process) {
            $processes[] = $_process;
        }
        
        return $processes;
    }
    
    /**
     * Retreive the indexer model.
     *
     * @return Mage_Index_Model_Indexer
     * @author Joseph McDermott <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getModel()
    {
        return Mage::getSingleton('index/indexer');
    }
}