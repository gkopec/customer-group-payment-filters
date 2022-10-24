<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Model;

use GalacticLabs\CustomerGroupPaymentFilters\Api\Data\ShippingFilterInterface;
use GalacticLabs\CustomerGroupPaymentFilters\Api\ShippingFilterRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ShippingFilterRepository implements ShippingFilterRepositoryInterface
{
    private $shippingFilterFactory;

    public function __construct(
        ShippingFilterFactory $shippingFilterFactory
    )
    {
        $this->shippingFilterFactory = $shippingFilterFactory;
    }

    /**
     * @param int $customerGroupId
     * @return ShippingFilterInterface
     */
    public function getByCustomerGroupId($customerGroupId)
    {
        $shippingFilter = $this->shippingFilterFactory->create();
        $shippingFilter->getResource()->load($shippingFilter, $customerGroupId);

        if($shippingFilter->getId() == null){
            $shippingFilter->setDisallowedShippingOptions([]);
        }

        return $shippingFilter;
    }

    /**
     * @param ShippingFilterInterface $shippingFilter
     * @return ShippingFilterInterface
     */
    public function save(ShippingFilterInterface $shippingFilter)
    {
        $shippingFilter->getResource()->save($shippingFilter);

        return $shippingFilter;
    }

    /**
     * @param ShippingFilterInterface $shippingFilter
     * @return void
     */
    public function delete(ShippingFilterInterface $shippingFilter)
    {
        $shippingFilter->getResource()->delete($shippingFilter);
    }
}
