<?php

namespace ReversIo\RMA\Model;

class BrandManagement
{
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
