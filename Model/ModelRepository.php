<?php

namespace ReversIo\RMA\Model;

class ModelRepository
{
    protected $reversIoClient;

    protected $modelIds;

    protected $brandRepository;

    protected $modelTypeRepository;

    protected $scopeConfig;

    public function __construct(
        \ReversIo\RMA\Gateway\Client $reversIoClient,
        \ReversIo\RMA\Model\BrandRepository $brandRepository,
        \ReversIo\RMA\Model\ModelTypeRepository $modelTypeRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->reversIoClient = $reversIoClient;
        $this->brandRepository = $brandRepository;
        $this->modelTypeRepository = $modelTypeRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function getModelIds()
    {
        return $this->modelIds;
    }

    public function getList()
    {
        $result = $this->reversIoClient->retrieveModelTypes();
        return $result;
    }

    public function getModelBySku($sku)
    {
        return $this->reversIoClient->retrieveModelBySKU($sku);
    }

    public function saveModel(\Magento\Catalog\Model\Product $product)
    {
        // IF THIS REPOSITORY IS CALLED IN A BATCH WE SUPPOSE THAT IT IS NOT NECESSARY TO SAVE A PRODUCT SEVERAL TIMES
        if (!isset($this->modelIds[$product->getSku()])) {
            $brandName = $this->retrieveBrandNameFromProduct($product);
            $modelTypeKey = $this->retrieveModelTypeKeyFromProduct($product);

            $brand = $this->brandRepository->saveBrand($brandName);
            $modelType = $this->modelTypeRepository->getModelTypeByKey($modelTypeKey);

            $model = $this->getModelBySku($product->getSku());

            if (!empty($model)) {
                try {
                    $model = $this->reversIoClient->updateModel($model['id'], $product, $brand['id'], $modelType['id']);
                } catch(\Exception $e) {
                    //TODO Log
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
        // TODO
        return $product->getData('reversio_modeltype');
    }

    public function retrieveBrandNameFromProduct(\Magento\Catalog\Model\Product $product)
    {
        $attributeBrand = $product->getResource()->getAttribute(
            $this->scopeConfig->getValue('reversio_rma/mapping/brand_attribute_code')
        );

        return $attributeBrand && $attributeBrand->getId()
            ? $attributeBrand->getFrontend()->getValue($product)
            : \ReversIo\RMA\Helper\Constants::UNKNOWN_BRAND_NAME;
    }
}