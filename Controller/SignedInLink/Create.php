<?php

namespace ReversIo\RMA\Controller\SignedInLink;

class Create extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;

    protected $magentoOrderRepository;

    protected $orderManagement;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderRepository $magentoOrderRepository,
        \ReversIo\RMA\Model\OrderManagement $orderManagement
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->orderManagement = $orderManagement;
    }

    public function execute()
    {
        $order = $this->magentoOrderRepository->get($this->getRequest()->getParam('order_id'));

        if (!$this->orderManagement->isOrderFromCustomer($order, $this->customerSession->getCustomerId())
         || !$this->orderManagement->isOrderReturnable($order)) {
            throw new \Magento\Framework\Exception\NotFoundException(__('Page not found.'));
        }

        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        try {
            $link = $this->orderManagement->createSignedInLink($order);
            $result->setJsonData(json_encode(['link' => $link]));
        } catch (\Exception $e) {
            $result->setJsonData(json_encode(['error' => true, 'message' => $e->getMessage()]));
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $result;
    }
}
