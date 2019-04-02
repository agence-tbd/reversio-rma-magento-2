<?php

namespace ReversIo\RMA\Model;

class OrderManagement
{
    protected $customerCollectionFactory;

    protected $modelRepository;

    protected $reversIoClient;

    protected $scopeConfig;

    protected $resourceHelper;

    public function __construct(
        \ReversIo\RMA\Model\ModelRepository $modelRepository,
        \ReversIo\RMA\Gateway\Client $reversIoClient,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ReversIo\RMA\Model\ResourceModel\Helper $resourceHelper
    ) {
        $this->modelRepository = $modelRepository;
        $this->reversIoClient = $reversIoClient;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resourceHelper = $resourceHelper;
    }

    protected function getCustomerFromOrder(\Magento\Sales\Model\Order $order)
    {
        if ($order->getCustomerId()) {
            return $this->customerCollectionFactory->create()
                ->addAttributeToFilter('entity_id', ['in' => [$order->getCustomerId()]])
                ->addAttributeToSelect('*')
                ->getFirstItem();
        } else {
            return $this->customerCollectionFactory->create()
                ->getNewEmptyItem();
        }
    }

    public function syncOrder(\Magento\Sales\Model\Order $order)
    {
        try {
            $skus = array_map(
                function ($item) {
                    return $item->getSku();
                },
                $order->getAllVisibleItems()
            );

            $this->modelRepository->saveModelsBySkus($skus, $order->getStoreId());

            $result = $this->reversIoClient->importOrder(
                $order,
                $this->getCustomerFromOrder($order),
                $this->modelRepository->getModelIds()
            );

            $this->resourceHelper->updateOrderReversIoSyncStatus(
                $order->getId(),
                \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_SYNC_SUCCESS
            );
            return $result;
        } catch (\Exception $e) {
            $this->resourceHelper->updateOrderReversIoSyncStatus(
                $order->getId(),
                \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_SYNC_ERROR
            );
            throw $e;
        }
    }

    public function createSignedInLink(\Magento\Sales\Model\Order $order)
    {
        $gatewayOrder = null;

        try {
            $gatewayOrder = $this->reversIoClient->retrieveOrder($order->getIncrementId());
        } catch (\Exception $ex) {
            // MEANS ORDER DOES NOT EXISTS OR ISSUES WHEN CONNECT TO REVERSIO
        }

        if (empty($gatewayOrder)) {
            $gatewayOrder = [
                'orderId' => $this->syncOrder($order)
            ];
        }

        return $this->reversIoClient->createSignedInLink($gatewayOrder['orderId']);
    }

    public function isOrderReturnable(\Magento\Sales\Model\Order $order)
    {
        $rmaAllowedOrderStatuses = $this->scopeConfig->getValue('reversio_rma/mapping/rma_allowed_order_statuses');
        $syncOrderStartDate = $this->scopeConfig->getValue('reversio_rma/mapping/sync_order_start_date');

        if (empty($rmaAllowedOrderStatuses)) {
            return false;
        }

        if (empty($syncOrderStartDate)) {
            return in_array($order->getStatus(), explode(',', $rmaAllowedOrderStatuses));
        } else {
            return in_array($order->getStatus(), explode(',', $rmaAllowedOrderStatuses))
                && strtotime($syncOrderStartDate) <= strtotime($order->getCreatedAt());
        }
    }

    public function isOrderFromCustomer(\Magento\Sales\Model\Order $order, $customerId)
    {
        return $customerId == $order->getCustomerId();
    }
}
