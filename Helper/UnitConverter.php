<?php

namespace ReversIo\RMA\Helper;

class UnitConverter
{
    protected $scopeConfig;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }
    
    public function convertDimensionData($dimension)
    {
        return (int)$dimension;
    }
    
    public function convertWeightData($weight)
    {
        return (float)$weight;
    }
}

