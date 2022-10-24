<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Model;

use GalacticLabs\CustomerGroupPaymentFilters\Api\Data\ShippingFilterInterface;
use GalacticLabs\CustomerGroupPaymentFilters\Model\ResourceModel\ShippingFilter as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class ShippingFilter extends AbstractModel implements ShippingFilterInterface
{
    const CUSTOMER_GROUP_ID = 'customer_group_id';
    const DISALLOWED_SHIPPING_OPTIONS = 'disallowed_shipping_options';

    /**
     * Assign the resource model for persistence.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @return int
     */
    public function getCustomerGroupId()
    {
        return $this->_getData(self::CUSTOMER_GROUP_ID);
    }

    /**
     * @param int $customerGroupId
     * @return void
     */
    public function setCustomerGroupId($customerGroupId)
    {
        $this->setData(self::CUSTOMER_GROUP_ID, $customerGroupId);
    }

    /**
     * @return string[]
     */
    public function getDisallowedShippingOptions()
    {
        return unserialize(
            $this->_getData(self::DISALLOWED_SHIPPING_OPTIONS)
        );
    }

    /**
     * @param string[] $shippingOptions
     * @return void
     */
    public function setDisallowedShippingOptions(array $shippingOptions)
    {
        $this->setData(self::DISALLOWED_SHIPPING_OPTIONS, serialize($shippingOptions));
    }
}
