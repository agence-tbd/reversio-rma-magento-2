<?php

namespace ReversIo\RMA\Model;

class ModelRepository
{
    protected $reversIoClient;

    protected $modelIds;

    protected $brandRepository;

    protected $modelTypeRepository;

    protected $brandManagement;

    protected $modelTypeManagement;

    protected $productCollectionFactory;

    protected $imageFactory;

    protected $appEmulation;

    public function __construct(
        \ReversIo\RMA\Gateway\Client $reversIoClient,
        \ReversIo\RMA\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \ReversIo\RMA\Model\BrandRepository $brandRepository,
        \ReversIo\RMA\Model\ModelTypeRepository $modelTypeRepository,
        \ReversIo\RMA\Model\ModelTypeManagement $modelTypeManagement,
        \ReversIo\RMA\Model\BrandManagement $brandManagement,
        \Magento\Catalog\Helper\ImageFactory $imageFactory,
        \Magento\Store\Model\App\Emulation $appEmulation
    ) {
        $this->reversIoClient = $reversIoClient;
        $this->brandRepository = $brandRepository;
        $this->modelTypeRepository = $modelTypeRepository;
        $this->brandManagement = $brandManagement;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageFactory = $imageFactory;
        $this->appEmulation = $appEmulation;
        $this->modelTypeManagement = $modelTypeManagement;
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
            $brandName = $this->brandManagement->retrieveBrandNameFromProduct($product, $storeId);
            $modelTypeKey = $this->modelTypeManagement->retrieveModelTypeKeyFromProduct($product, $storeId);

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
}
