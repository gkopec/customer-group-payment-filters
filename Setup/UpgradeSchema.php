<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('customer_group_disallowed_shipping_options'))
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Customer Group ID'
                )
                ->addColumn(
                    'disallowed_shipping_options',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BLOB,
                    '2K',
                    ['nullable' => false],
                    'Disallowed Shipping Options'
                )->setComment("Customer Group Disallowed Shipping Options");

            $setup->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
