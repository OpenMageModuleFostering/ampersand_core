<?php
class Ampersand_Api_Model_Catalog_Category
{
    /**
     * Fields which can fall back to default setting
     *
     * @var array $_userPostDataConfigFields
     */
    protected $_usePostDataConfigFields = array(
        'default_sort_by',
        'available_sort_by',
    );
    
    /**
     * Creates a category
     *
     * @param array $categoryData 
     * @return Mage_Catalog_Model_Category
     * @author Josh Di Fabio <josh.difabio@ampersandcommerce.com>
     */
    public function create(array $categoryData)
    {
        if (!array_key_exists('parent_id', $categoryData)) {
            Mage::throwException('parent_id was not specified in category data.');
        }
        if (!array_key_exists('store_id', $categoryData)) {
            Mage::throwException('store_id was not specified in category data.');
        }

        $parentCategory = Mage::getModel('catalog/category')
            ->setStoreId($categoryData['store_id'])
            ->load($categoryData['parent_id']);

        // some fields are required but by using post data config they will 'use default value'
        foreach ($this->_usePostDataConfigFields as $_field) {
            if (!array_key_exists($_field, $categoryData) || empty($categoryData[$_field])) {
                $categoryData[$_field] = '';
                $categoryData['use_post_data_config'][] = $_field;
            }
        }
        
        $category = Mage::getModel('catalog/category')
            ->setData($categoryData)
            ->setPath(implode('/', $parentCategory->getPathIds()));

        $category->setAttributeSetId($category->getDefaultAttributeSetId());

        if (true !== ($validate = $category->validate())) {
            foreach ($validate as $code => $error) {
                if ($error === true) {
                    Mage::throwException("Attribute $code is required.");
                } else {
                    Mage::throwException($error);
                }
            }
        }

        $category->save();

        return $category;
    }
    
    /**
     * recursive method to create a tree like category nodes array into magento.
     * $tree= Arrary(
     *      'nodeName' => Array(
     *             '_attributes' => Array(
     *                      'is_active' => true,
     *                      'include_in_menu' => true,
     *                      'otherAttribute'  => 'blablabla',
     *                          ),
     *             '_children'   => Array(
     *                      'childNodeName' => Array( ... )
     *                          ),
     *                  ),
     * );
     * 
     * @param array $tree
     * @param type $parentId 
     */
    public function createFromArray(array $tree, $parentId)
    {
        foreach ($tree['_children'] as $key => $value) {
            $categoryDataArray = $this->_populateCategoryData($key, $value['_attributes']);
            $categoryDataArray['parent_id']=$parentId;
            $createdCategory = $this->create($categoryDataArray);

            if (array_key_exists('_children', $value)) {
                $this->createFromArray($value, $createdCategory->getId());
            }
        }
    }

    protected function _populateCategoryData($name, $attributes)
    {
        $categoryDataArray = Array();

        $categoryDataArray['name'] = $name;
        $categoryDataArray['is_active'] = true;
        $categoryDataArray['include_in_menu'] = true;
        $categoryDataArray['store_id'] = null;
        $categoryDataArray['parent_id'] = null;
        $categoryDataArray['url_key'] = null;

        foreach ($attributes as $key => $value) {
            $categoryDataArray[$key] = $value;
        }
        return $categoryDataArray;
    }
}