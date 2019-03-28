<?php

namespace ReversIo\RMA\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'), 
                'reversio_sync_status', 
                [
                    'type' =>  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'ReversIo Sync Status',
                    'nullable' => false,
                    'default' => \ReversIo\RMA\Helper\Constants::REVERSIO_SYNC_STATUS_NOT_SYNC,
                ]
            );

        $installer->endSetup();
    }
}
