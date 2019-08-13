<?php
class Ampersand_PageCache_Model_Container_NoCache
    extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Retrieve container individual cache id
     *
     * @return string
     * @author Joseph McDeromtt <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _getCacheId()
    {
        return $this->_placeholder->getAttribute('placeholder') . 
            md5(
                $this->_placeholder->getAttribute('cache_id')
                . $this->_getCookieValue('frontend', '')
            );
    }
    
    /**
     * Render block content from placeholder.
     *
     * @return string
     * @author Joseph McDeromtt <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _renderBlock()
    {
        $blockClass = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $blockClass;
        $block->setTemplate($template);
        
        return $block->toHtml();
    }
    
    /**
     * Do not save data to cache storage.
     *
     * @param string $data
     * @param string $id
     * @param array $tags
     * @author Joseph McDeromtt <joseph.mcdermott@ampersandcommerce.com>
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        $lifetime = 0;
        parent::_saveCache($data, $id, $tags, $lifetime);
    }
}