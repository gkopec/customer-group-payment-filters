<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Api;

use GalacticLabs\CustomerGroupPaymentFilters\Api\Data\ShippingFilterInterface;

interface ShippingFilterRepositoryInterface
{
    /**
     * @param int $customerGroupId
     * @return ShippingFilterInterface
     */
    public function getByCustomerGroupId($customerGroupId);

    /**
     * @param ShippingFilterInterface $shippingFilter
     * @return ShippingFilterInterface
     */
    public function save(ShippingFilterInterface $shippingFilter);

    /**
     * @param ShippingFilterInterface $shippingFilter
     * @return void
     */
    public function delete(ShippingFilterInterface $shippingFilter);
}
