<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Plugin\Block\Aminhtml\Group\Edit;

use Closure;
use Magento\Framework\Registry;
use Magento\Customer\Block\Adminhtml\Group\Edit\Form as CustomerGroupForm;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config\Source\Allmethods;

class FormPlugin
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;
    /**
     * @var Registry
     */
    private $coreRegistry;
    /**
     * @var \Magento\Customer\Api\Data\GroupInterfaceFactory
     */
    private $groupDataFactory;
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;
    private ScopeConfigInterface $scopeConfig;
    private Allmethods $shippingAllmethods;

    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\Data\GroupInterfaceFactory $groupDataFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        ScopeConfigInterface $scopeConfig,
        Allmethods $shippingAllmethods
    )
    {
        $this->paymentHelper = $paymentHelper;
        $this->coreRegistry = $coreRegistry;
        $this->groupDataFactory = $groupDataFactory;
        $this->groupRepository = $groupRepository;
        $this->scopeConfig = $scopeConfig;
        $this->shippingAllmethods = $shippingAllmethods;
    }

    public function aroundGetFormHtml(CustomerGroupForm $subject, Closure $proceed)
    {
        $customerGroup = $this->getCustomerGroup();
        $form = $subject->getForm();

        if (is_object($form)) {
            $disabledPaymentMethods = $this->getDisallowedPaymentOptions($customerGroup);
            $this->addField($form, $disabledPaymentMethods, $this->getPaymentMethodsList(), 'Payment');

            $disabledShippingMethods = $this->getDisallowedShippingOptions($customerGroup);
            $this->addField($form, $disabledShippingMethods, $this->getShippingMethodsList(), 'Shipping');

            $subject->setForm($form);
        }

        return $proceed();
    }

    protected function addField($form, $options, $values, $label)
    {
        $code = \Safe\sprintf('disallowed_%s_options', strtolower($label));
        $fieldset = $form->addFieldset(
            $code.'_fieldset',
            [
                'legend' => __("Disallowed $label Options")
            ]
        );

        $fieldset->addField(
            $code.'_multiselect',
            'multiselect',
            [
                'name' => $code.'[]',
                'label' => __("Disallowed $label Options"),
                'id' => $code,
                'title' => __("Disallowed $label Options"),
                'required' => false,
                'note' => 'Multi select the'. strtolower($label) .'options that you do NOT want this customer group to be able to use.',
                'value' => $options,
                'values' => $values
            ]
        );
    }

    /**
     * Use the payment helper to gather details about payment options available.
     * Specifically we get an array back of key/value pairs of payment code against
     * its description. We then just reformat that so we can use it nicely in the
     * multi-select.
     *
     * @return array
     */
    private function getPaymentMethodsList()
    {
        $paymentOptions = $this->paymentHelper->getPaymentMethodList();

        return array_filter(array_map(function ($paymentMethodCode, $paymentMethodDescription) {
            if($paymentMethodDescription == '') return;

            return array(
                'value' => $paymentMethodCode,
                'label'  => "{$paymentMethodDescription} ({$paymentMethodCode})"
            );
        }, array_keys($paymentOptions), $paymentOptions));
    }

    private function getShippingMethodsList()
    {
        $carriers = $this->scopeConfig->getValue(
            'carriers',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return array_filter(array_map(function ($code, $carrier) {
            return array(
                'value' => $code,
                'label'  => $carrier['title']
            );
        }, array_keys($carriers), $carriers));
    }

    /**
     * If there is no current customer group ID then we are creating a
     * new customer group.
     *
     * @return \Magento\Customer\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerGroup()
    {
        $groupId = $this->coreRegistry->registry(RegistryConstants::CURRENT_GROUP_ID);

        $customerGroup = $groupId === null
            ? $this->groupDataFactory->create()
            : $this->groupRepository->getById($groupId);

        return $customerGroup;
    }

    /**
     * Get the disallowed payment options for the group. If its a new
     * group we'll send back an empty array. This will be used to populate
     * the multi select list.
     *
     * @param $customerGroup
     * @return array
     */
    private function getDisallowedPaymentOptions($customerGroup)
    {
        if ($customerGroup->getExtensionAttributes() !== null && $customerGroup->getExtensionAttributes()->getDisallowedPaymentOptions() !== null) {
            $disallowedPaymentMethods = $customerGroup->getExtensionAttributes()
                ->getDisallowedPaymentOptions()
                ->getDisallowedPaymentOptions();
        } else {
            $disallowedPaymentMethods = [];
        }

        return $disallowedPaymentMethods;
    }

    private function getDisallowedShippingOptions($customerGroup)
    {
        if ($customerGroup->getExtensionAttributes() !== null && $customerGroup->getExtensionAttributes()->getDisallowedShippingOptions() !== null) {
            $disallowedShippingOptions = $customerGroup->getExtensionAttributes()
                ->getDisallowedShippingOptions()
                ->getDisallowedShippingOptions();
        } else {
            $disallowedShippingOptions = [];
        }

        return $disallowedShippingOptions;
    }
}
