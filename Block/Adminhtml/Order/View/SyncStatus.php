<?php

namespace ReversIo\RMA\Block\Adminhtml\Order\View;

class SyncStatus extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    public function getOrderSyncStatus()
    {
        switch($this->getOrder()->getData('reversio_sync_status')) {
            case \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_NOT_SYNC:
                return __('Not Sync');
            case \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_SYNC_ERROR:
                return __('Sync Error');
            case \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_SYNC_SUCCESS:
                return __('Sync Success');
            default:
                'N/A';
        }
    }
}