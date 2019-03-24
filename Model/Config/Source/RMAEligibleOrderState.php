<?php

namespace ReversIo\RMA\Model\Config\Source;

class RMAEligibleOrderState implements \Magento\Framework\Option\ArrayInterface
{
    protected $orderConfig;

    public function __construct(
        \Magento\Sales\Model\Order\Config $orderConfig
    )
    {
        $this->orderConfig = $orderConfig;
    }

    public function toOptionArray()
    {
        $result = [];

        foreach ($this->orderConfig->getStates() as $stateKey => $stateLabel) {
            $result[] = ['value' => $stateKey, 'label' => $stateLabel];
        }

        return $result;
    }
}

