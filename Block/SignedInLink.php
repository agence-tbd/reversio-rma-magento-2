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
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderManagement = $orderManagement;
        $this->magentoOrderRepository = $magentoOrderRepository;
        $this->customerSession = $customerSession;
    }

    protected function _toHtml()
    {
        $order = $this->getOrder();

        if ($this->orderManagement->isOrderFromCustomer($order, $this->customerSession->getCustomerId())
         && $this->orderManagement->isOrderReturnable($order)) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    protected function getOrder()
    {
        // NB : this function handles the fact it could be called several times, there will be only one call in the DB
        return $this->magentoOrderRepository->get($this->getOrderId());
    }

    protected function getSyncOrder()
    {
        return $this->orderManagement->retrieveSyncOrder($this->getOrder());
    }

    public function getMessageLabel()
    {
        $order = $this->getOrder();

        if ($order->getData('reversio_sync_status') == \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_SYNC_ERROR) {
            return __('Sorry, something went wrong. Please contact the seller using the dedicated contact form.');
        }

        $syncOrder = $this->getSyncOrder();

        if (!$syncOrder 
        || (!$this->orderManagement->hasSyncOrderOpenFiles($syncOrder) 
          && $this->orderManagement->isSyncOrderOpenForClaims($syncOrder))) {
            return __('If you want to return your product, please click on the button below. You will be forwarded to the returns platform.');
        } elseif ($this->orderManagement->hasSyncOrderOpenFiles($syncOrder)) {
            return __('To check the status of your ongoing returns request or open new return requests, please click on the button below.');
        } elseif (!$this->orderManagement->isSyncOrderOpenForClaims($syncOrder)) {
            return __('Unfortunately the products of this order are not available for return anymore. In case of error please contact the seller.');
        }
    }

    public function getButtonLabel()
    {
        $order = $this->getOrder();

        if ($order->getData('reversio_sync_status') == \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_SYNC_ERROR) {
            return false;
        }

        $syncOrder = $this->getSyncOrder();

        if (!$syncOrder 
        || (!$this->orderManagement->hasSyncOrderOpenFiles($syncOrder) 
          && $this->orderManagement->isSyncOrderOpenForClaims($syncOrder))) {
            return __('Request return');
        } elseif ($this->orderManagement->hasSyncOrderOpenFiles($syncOrder)) {
            return __('View returns');
        } elseif (!$this->orderManagement->isSyncOrderOpenForClaims($syncOrder)) {
            return false;
        }
    }
}
