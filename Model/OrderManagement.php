<?php

namespace ReversIo\RMA\Model;

class OrderManagement
{
    protected $productCollectionFactory;

    protected $customerCollectionFactory;

    protected $modelRepository;

    protected $reversIoClient;

    public function __construct(
        \ReversIo\RMA\Model\ModelRepository $modelRepository,
        \ReversIo\RMA\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \ReversIo\RMA\Gateway\Client $reversIoClient,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    )
    {
        $this->modelRepository = $modelRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->reversIoClient = $reversIoClient;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    public function syncOrder(\Magento\Sales\Model\Order $order)
    {
        $skus = [];
        $items = $order->getAllVisibleItems();

        foreach ($items as $item) {
            $skus[] = $item->getSku();
        }

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToFilter('sku', ['in' => $skus])
            ->addAttributeToSelect('*');

        foreach ($productCollection as $product) {
            $this->modelRepository->saveModel($product);
        }

        $customer = null;
        if ($order->getCustomerId()) {
            $customer = $this->customerCollectionFactory->create()
                ->addAttributeToFilter('entity_id', ['in' => [$order->getCustomerId()]])
                ->addAttributeToSelect('*')
                ->getFirstItem();
        } else {
            $customer = $this->customerCollectionFactory->create()
                ->getNewEmptyItem();
        }

        return $this->reversIoClient->importOrder(
            $order, $customer, $this->modelRepository->getModelIds()
        );
    }

    public function createSignedInLink(\Magento\Sales\Model\Order $order)
    {
        $gatewayOrder = $this->reversIoClient->retrieveOrder($order->getIncrementId());

        if(empty($gatewayOrder)) {
            $gatewayOrder = [
                'orderId' => $this->syncOrder($order)
            ];
        }

        return $this->reversIoClient->createSignedInLink($gatewayOrder['orderId']);
    }
    
    public function isOrderReturnable()
    {
        
    }
}

