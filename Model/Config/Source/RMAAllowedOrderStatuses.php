<?php

namespace ReversIo\RMA\Model\Config\Source;

class RMAAllowedOrderStatuses implements \Magento\Framework\Option\ArrayInterface
{
    protected $orderConfig;

    public function __construct(
        \Magento\Sales\Model\Order\Config $orderConfig
    ) {
        $this->orderConfig = $orderConfig;
    }

    public function toOptionArray()
    {
        $result = [];

        foreach ($this->orderConfig->getStatuses() as $statusKey => $statusLabel) {
            $result[] = ['value' => $statusKey, 'label' => $statusLabel];
        }

        return $result;
    }
}
