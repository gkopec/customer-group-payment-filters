<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Plugin\Model;

use GalacticLabs\CustomerGroupPaymentFilters\Api\PaymentFilterRepositoryInterface;
use GalacticLabs\CustomerGroupPaymentFilters\Api\ShippingFilterRepositoryInterface;
use GalacticLabs\CustomerGroupPaymentFilters\Api\Data\PaymentFilterInterfaceFactory;
use GalacticLabs\CustomerGroupPaymentFilters\Api\Data\ShippingFilterInterfaceFactory;
use Magento\Customer\Api\Data\GroupExtensionFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\RequestInterface;

class GroupRepository
{
    /**
     * @var PaymentFilterRepositoryInterface
     */
    private $paymentFilterRepository;
    /**
     * @var GroupExtensionFactory
     */
    private $extensionFactory;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var PaymentFilterInterfaceFactory
     */
    private $filterInterfaceFactory;
    private ShippingFilterRepositoryInterface $shippingFilterRepository;
    private ShippingFilterInterfaceFactory $shippingFilterFactory;

    public function __construct(
        RequestInterface $request,
        PaymentFilterRepositoryInterface $paymentFilterRepository,
        ShippingFilterRepositoryInterface $shippingFilterRepository,
        GroupExtensionFactory $extensionFactory,
        PaymentFilterInterfaceFactory $filterInterfaceFactory,
        ShippingFilterInterfaceFactory $shippingFilterFactory
    )
    {
        $this->request = $request;
        $this->paymentFilterRepository = $paymentFilterRepository;
        $this->shippingFilterRepository = $shippingFilterRepository;
        $this->extensionFactory = $extensionFactory;
        $this->filterInterfaceFactory = $filterInterfaceFactory;
        $this->shippingFilterFactory = $shippingFilterFactory;
    }

    /**
     * Here we hook into the repository getById method in order to add our new attribute
     * to the returned data.
     *
     * @param GroupRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\GroupInterface $customerGroup
     * @return \Magento\Customer\Api\Data\GroupInterface
     */
    public function afterGetById(GroupRepositoryInterface $subject, \Magento\Customer\Api\Data\GroupInterface $customerGroup)
    {
        $extensionAttributes = $customerGroup->getExtensionAttributes();
        if($extensionAttributes == null){
            $extensionAttributes = $this->extensionFactory->create();
        }

        $disallowedPaymentOptions = $this->paymentFilterRepository->getByCustomerGroupId($customerGroup->getId());
        $disallowedShippingOptions = $this->shippingFilterRepository->getByCustomerGroupId($customerGroup->getId());
        $extensionAttributes
            ->setDisallowedPaymentOptions($disallowedPaymentOptions)
            ->setDisallowedShippingOptions($disallowedShippingOptions);

        $customerGroup->setExtensionAttributes($extensionAttributes);

        return $customerGroup;
    }

    /**
     * After repo save we'll try to save our disallowed payment methods if
     * we set any.
     *
     * @param GroupRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\GroupInterface $customerGroup
     * @return \Magento\Customer\Api\Data\GroupInterface
     */
    public function afterSave(GroupRepositoryInterface $subject, \Magento\Customer\Api\Data\GroupInterface $customerGroup){

        try {
            $disallowedPaymentOptions = $this->request->getParam('disallowed_payment_options');

            if($disallowedPaymentOptions == null){
                $disallowedPaymentOptions = [];
            }

            $disallowedShippingOptions = $this->request->getParam('disallowed_shipping_options');

            if($disallowedShippingOptions == null){
                $disallowedShippingOptions = [];
            }


            $paymentFilter = $this->filterInterfaceFactory->create();
            $paymentFilter->setCustomerGroupId($customerGroup->getId());
            $paymentFilter->setDisallowedPaymentOptions($disallowedPaymentOptions);

            $shippingFilter = $this->shippingFilterFactory->create();
            $shippingFilter->setCustomerGroupId($customerGroup->getId());
            $shippingFilter->setDisallowedShippingOptions($disallowedShippingOptions);

            $this->paymentFilterRepository->save($paymentFilter);
            $this->shippingFilterRepository->save($shippingFilter);
        } catch (\Exception $e) { /** TODO: Do something with the exception */}

        return $customerGroup;
    }

    /**
     * Delete the payment filter before the customer group is deleted. We do this here
     * so we know which customer group is being deleted. Unfortunately the deletion
     * of a customer group returns a bool so we can't do it as an after plugin.
     *
     * @param GroupRepositoryInterface $subject
     * @param $id
     */
    public function beforeDeleteById(GroupRepositoryInterface $subject, $id){
        $paymentFilter = $this->paymentFilterRepository->getByCustomerGroupId($id);

        if($paymentFilter->getCustomerGroupId() != null){
            $this->paymentFilterRepository->delete($paymentFilter);
        }

        $shippingFilter = $this->shippingFilterFactory->getByCustomerGroupId($id);

        if($shippingFilter->getCustomerGroupId() != null){
            $this->shippingFilterRepository->delete($shippingFilter);
        }
    }

}
