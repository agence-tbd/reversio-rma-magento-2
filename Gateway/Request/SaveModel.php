<?php

namespace ReversIo\RMA\Gateway\Request;

abstract class SaveModel extends AbstractRequest
{
    protected $product;

    protected $brandId;

    protected $modelTypeId;

    protected $scopeConfig;

    protected $helperUnitConverter;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ReversIo\RMA\Helper\UnitConverter $helperUnitConverter
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperUnitConverter = $helperUnitConverter;
    }

    public function setProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->product = $product;
        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setBrandId($brandId)
    {
        $this->brandId = $brandId;
        return $this;
    }

    public function setModelTypeId($modelTypeId)
    {
        $this->modelTypeId = $modelTypeId;
        return $this;
    }

    public function getData()
    {
        if (!isset($this->product)) {
            throw new \Exception('Unkown product.');
        }

        return [
            'brandId' => $this->brandId,
            'modelTypeId' => $this->modelTypeId,
            'label' => $this->product->getName(),
            'sKU' => $this->product->getSku(),
            'eANs' => [], // TODO
            'dimension' => [
                'lengthInCm' => $this->helperUnitConverter->convertDimensionData($this->product->getData('ts_dimensions_length')),
                'widthInCm' => $this->helperUnitConverter->convertDimensionData($this->product->getData('ts_dimensions_width')),
                'heightInCm' => $this->helperUnitConverter->convertDimensionData($this->product->getData('ts_dimensions_height')),
            ],
            'photoUrl' => $this->product->getReversioImageUrl(),
            'additionalInformation'  => $this->getAdditionalInformation(),
            'state' => 'New',
            'weight' => $this->helperUnitConverter->convertWeightData($this->product->getWeight())
        ];
    }

    public function getAdditionalInformation()
    {
        // TODO Check data with product types (virtual product may not be returnable....)
        return [
            'isReturnable' => true,
            'isRepairable' => true,
            'isTransportable' => true,
            'isSerializable' => true,
            'isOnSiteInterventionPossible' => true,
            'isCumbersome' => true,
        ];
    }
}