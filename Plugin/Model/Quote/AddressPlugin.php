<?php

namespace GalacticLabs\CustomerGroupPaymentFilters\Plugin\Model\Quote;

use Magento\Quote\Model\Quote\Address;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\GroupRepositoryInterface;

class AddressPlugin
{
    private ScopeConfigInterface $_scopeConfig;
    private StoreManagerInterface $storeManager;
    private Session $customerSession;
    private GroupRepositoryInterface $groupRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Session $customerSession,
        GroupRepositoryInterface $groupRepository
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
    }

    public function beforeRequestShippingRates(Address $subject)
    {
        if($disabledMethods = $this->getDisabledMethods()) {
            $limitCarriers = [];
            foreach ($this->getAllCarriers($subject) as $code => $carrier) {
                if(!in_array($code, $disabledMethods)) {
                    $limitCarriers[] = $code;
                }
            }
            $subject->setLimitCarrier($limitCarriers);
        }
    }

    protected function getAllCarriers($address)
    {
        $storeId = $address->getQuote()->getStoreId() ?: $this->storeManager->getStore()->getId();

        return $this->_scopeConfig->getValue(
            'carriers',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    protected function getDisabledMethods()
    {
        $customerGroupId = $this->customerSession->getCustomer()->getGroupId();
        $customerGroup = $this->groupRepository->getById($customerGroupId);
        return $customerGroup->getExtensionAttributes()
            ->getDisallowedShippingOptions()
            ->getDisallowedShippingOptions();
    }
}
