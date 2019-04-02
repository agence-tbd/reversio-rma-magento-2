<?php

namespace ReversIo\RMA\Helper;

class UnitConverter
{
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function convertDimensionData($dimension)
    {
        // THIS TEST IS BECAUSE : Temando\Shipping\Ui\DataProvider\Product\Form\Modifier\Dimensions Line 79
        if ($this->scopeConfig->getValue('general/locale/weight_unit') == 'lbs') {
            return (int)($dimension * 2.54);
        } else {
            return (int)$dimension;
        }
    }

    public function convertWeightData($weight)
    {
        if ($this->scopeConfig->getValue('general/locale/weight_unit') == 'lbs') {
            return (float)($weight * 0.453592);
        } else {
            return (float)$weight;
        }
    }
}
