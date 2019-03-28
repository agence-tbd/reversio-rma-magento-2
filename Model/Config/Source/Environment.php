<?php

namespace ReversIo\RMA\Model\Config\Source;

class Environment implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $environments = [
            \ReversIo\RMA\Helper\Constants::REVERSIO_ENVIRONMENT_PROD,
            \ReversIo\RMA\Helper\Constants::REVERSIO_ENVIRONMENT_TEST,
            \ReversIo\RMA\Helper\Constants::REVERSIO_ENVIRONMENT_CUSTOM,
        ];

        $result = [];
        foreach ($environments as $environment) {
            $result[] = ['value' => $environment, 'label' => strtoupper($environment)];
        }

        return $result;
    }
}
