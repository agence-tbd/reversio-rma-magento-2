<?php

namespace ReversIo\RMA\Model;

class ModelTypeManagement
{
    protected $categoryCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function retrieveModelTypeKeyFromProduct(\Magento\Catalog\Model\Product $product, $storeId)
    {
        $modelType = $product->getData('reversio_modeltype');

        if (!empty($modelType)) {
            return $modelType;
        }

        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('entity_id', ['in' => $product->getCategoryIds()])
            ->addAttributeToSelect(['reversio_modeltype', 'name'])
            ->setStoreId($storeId);

        $categoryWithModelTypes = [];
        foreach ($categoryCollection as $category) {
            $tmpModelType = $category->getData('reversio_modeltype');

            if (empty($tmpModelType)) {
                $category->setData('reversio_modeltype', $category->getName());
            }

            $categoryWithModelTypes[] = $category;
        }

        if (empty($categoryWithModelTypes)) {
            return null;
        } else {
            $categoryWithMaxDepth = $categoryWithModelTypes[0];

            for ($i = 0; $i < count($categoryWithModelTypes); $i++) {
                $categoryIPath = explode('/', $categoryWithModelTypes[$i]->getPath());

                if (count($categoryIPath) > count(explode('/', $categoryWithMaxDepth->getPath()))) {
                    $categoryWithMaxDepth = $categoryWithModelTypes[$i];
                }

                for ($j = 0; $j < count($categoryWithModelTypes); $j++) {
                    $categoryJPath = explode('/', $categoryWithModelTypes[$j]->getPath());
                    // MEANS THAT 2 CATEGORIES ARE FROM DIFFERENT LEAVES : WE EXIT
                    if (count(array_diff($categoryIPath, $categoryJPath)) > 0
                     && count(array_diff($categoryJPath, $categoryIPath)) > 0) {
                        return null;
                    }
                }
            }

            return $categoryWithMaxDepth->getData('reversio_modeltype');
        }
    }
}
