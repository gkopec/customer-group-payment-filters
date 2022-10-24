<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PaymentFilter extends AbstractDb
{
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('customer_group_disallowed_payment_options', 'customer_group_id');
    }
}
