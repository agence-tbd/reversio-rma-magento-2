<?php

namespace ReversIo\RMA\Cron;

class SyncOrder
{
    protected $orderCollectionFactory;

    protected $orderManagement;

    protected $scopeConfig;

    protected $batchSize;

    protected $logger;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \ReversIo\RMA\Model\OrderManagement $orderManagement,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        $batchSize = 25
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderManagement = $orderManagement;
        $this->scopeConfig = $scopeConfig;
        $this->batchSize = $batchSize;
        $this->logger = $logger;
    }

    public function execute()
    {
        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('reversio_sync_status', ['in' => [
                \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_SYNC_ERROR,
                \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_NOT_SYNC
            ]])
            ->setPageSize($this->batchSize);

        $syncOrderStartDate = $this->scopeConfig->getValue('reversio_rma/mapping/sync_order_start_date');
        if ($syncOrderStartDate) {
            $orderCollection->addFieldToFilter('created_at', ['gteq' => $syncOrderStartDate]);
        }

        foreach ($orderCollection as $order) {
            if ($this->orderManagement->isOrderReturnable($order)) {
                try {
                    $this->orderManagement->syncOrder($order);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }
}
