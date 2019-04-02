<?php

namespace ReversIo\RMA\Model\ResourceModel;

class Helper extends \Magento\Framework\DB\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        $modulePrefix = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION
    ) {
        $this->_resource = $resource;
        $this->_modulePrefix = (string)$modulePrefix;
    }

    public function initOrderReversIoSyncStatus($syncStatus)
    {
        $connection = $this->getConnection();

        $data = [
            'reversio_sync_status' => $syncStatus
        ];

        $where['reversio_sync_status IS NULL'] = true;
        $connection->update($connection->getTableName('sales_order'), $data, $where);

        return $this;
    }
    
    public function updateOrderReversIoSyncStatus($orderId, $syncStatus)
    {
        $connection = $this->getConnection();

        $data = [
            'updated_at' => (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'reversio_sync_status' => $syncStatus
        ];

        $where['entity_id = ?'] = $orderId;
        $connection->update($connection->getTableName('sales_order'), $data, $where);

        return $this;
    }

    public function addLikeEscape($value, $options = [])
    {
        $value = $this->escapeLikeValue($value, $options);
        return new \Zend_Db_Expr($this->getConnection()->quote($value));
    }
}
