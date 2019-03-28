<?php

namespace ReversIo\RMA\Block;

class SignedInLink extends \Magento\Framework\View\Element\Template
{
    protected $orderManagement;

    protected $magentoOrderRepository;

    protected $customerSession;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \ReversIo\RMA\Model\OrderManagement $orderManagement,
        \Magento\Sales\Model\OrderRepository $magentoOrderRepository,
        \Magento\Customer\Model\Session $customerSession,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->orderManagement = $orderManagement;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->customerSession = $customerSession;
    }

    protected function _toHtml()
    {
        $order = $this->magentoOrderRepository->get($this->getRequest()->getParam('order_id'));
        
        if ($this->orderManagement->isOrderFromCustomer($order, $this->customerSession->getCustomerId()) 
         && $this->orderManagement->isOrderReturnable($order)) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
