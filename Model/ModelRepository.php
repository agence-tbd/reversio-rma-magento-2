<?php

namespace ReversIo\RMA\Model;

class ModelRepository
{
    protected $reversIoClient;

    protected $modelIds;

    protected $brandRepository;

    protected $modelTypeRepository;

    protected $scopeConfig;

    protected $categoryCollectionFactory;

    protected $productCollectionFactory;

    protected $imageFactory;

    protected $appEmulation;

    public function __construct(
        \ReversIo\RMA\Gateway\Client $reversIoClient,
        \ReversIo\RMA\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \ReversIo\RMA\Model\BrandRepository $brandRepository,
        \ReversIo\RMA\Model\ModelTypeRepository $modelTypeRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Helper\ImageFactory $imageFactory,
        \Magento\Store\Model\App\Emulation $appEmulation
    ) {
        $this->reversIoClient = $reversIoClient;
        $this->brandRepository = $brandRepository;
        $this->modelTypeRepository = $modelTypeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageFactory = $imageFactory;
        $this->appEmulation = $appEmulation;
    }

    public function getModelIds()
    {
        return $this->modelIds;
    }

    public function getModelBySku($sku)
    {
        return $this->reversIoClient->retrieveModelBySKU($sku);
    }

    public function saveModelsBySkus(array $skus, $storeId)
    {
        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToFilter('sku', ['in' => $skus])
            ->addStoreFilter($storeId)
            ->addAttributeToSelect('*')
            ->addCategoryIds();

        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        foreach ($productCollection as $product) {
            $product->setReversioImageUrl(
                $this->imageFactory->create()->init($product, 'cart_page_product_thumbnail')->getUrl()
            );
        }
        $this->appEmulation->stopEnvironmentEmulation();

        foreach ($productCollection as $product) {
            $this->saveModel($product, $storeId);
        }

        return $this;
    }

    public function saveModel(\Magento\Catalog\Model\Product $product, $storeId)
    {
        // IF THIS REPOSITORY IS CALLED IN A BATCH WE SUPPOSE THAT IT IS NOT NECESSARY TO SAVE A PRODUCT SEVERAL TIMES
        if (!isset($this->modelIds[$product->getSku()])) {
            $brandName = $this->retrieveBrandNameFromProduct($product, $storeId);
            $modelTypeKey = $this->retrieveModelTypeKeyFromProduct($product);

            $brand = $this->brandRepository->saveBrand($brandName);
            $modelType = $this->modelTypeRepository->getModelTypeByKey($modelTypeKey);
            $model = null;

            try {
                $model = $this->getModelBySku($product->getSku());
            } catch (\Exception $e) {
                // MEANS MODEL DOES NOT EXISTS OR ISSUES WHEN CONNECT TO REVERSIO
            }

            if (!empty($model)) {
                try {
                    $model = $this->reversIoClient->updateModel($model['id'], $product, $brand['id'], $modelType['id']);
                } catch (\Exception $e) {
                    // TODO Log
                }
            } else {
                $model = $this->reversIoClient->createModel($product, $brand['id'], $modelType['id']);
            }

            $this->modelIds[$product->getSku()] = $model['id'];
        }

        return $this;
    }

    public function retrieveModelTypeKeyFromProduct(\Magento\Catalog\Model\Product $product)
    {
        $modelType = $product->getData('reversio_modeltype');

        if (!empty($modelType)) {
            return $modelType;
        }

        $categoryCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToFilter('entity_id', ['in' => $product->getCategoryIds()])
            ->addAttributeToSelect('reversio_modeltype');

        $categoryWithModelTypes = [];
        foreach ($categoryCollection as $category) {
            $tmpModelType = $category->getData('reversio_modeltype');

            if (!empty($tmpModelType)) {
                $categoryWithModelTypes[] = $category;
            }
        }

        if (count($categoryWithModelTypes) == 0) {
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

    public function retrieveBrandNameFromProduct(\Magento\Catalog\Model\Product $product, $storeId)
    {
        $attributeBrand = $product->getResource()->getAttribute(
            $this->scopeConfig->getValue('reversio_rma/mapping/brand_attribute_code')
        );

        $brandName = $attributeBrand && $attributeBrand->getId()
            ? $attributeBrand->setStoreId($storeId)->getFrontend()->getValue($product)
            : null;

        return empty($brandName) ? \ReversIo\RMA\Helper\Constants::UNKNOWN_BRAND_NAME : $brandName;
    }
}
