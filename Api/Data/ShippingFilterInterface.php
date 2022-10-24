<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Api\Data;

interface ShippingFilterInterface
{
    /**
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * @param int $customerGroupId
     * @return void
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * @return string[]
     */
    public function getDisallowedShippingOptions();

    /**
     * @param string[] $shippingOptions
     * @return void
     */
    public function setDisallowedShippingOptions(array $shippingOptions);
}
