<?php

namespace Riversy\ShippingMethodRestrictions\Plugin\Model\Rate\Result;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Riversy\ShippingMethodRestrictions\Model\ConfigProvider;

class AfterGetAllRatesPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param ConfigProvider $configProvider
     * @param CustomerSession $customerSession
     */
    public function __construct(
        ConfigProvider $configProvider,
        CustomerSession $customerSession
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Result $subject
     * @param array $result
     *
     * @return array
     *
     * @throws LocalizedException
     */
    public function afterGetAllRates(Result $subject, $result)
    {
        if ($this->hasToRestrictByCustomerGroup()) {
            $this->restrictShippingMethods($result);
        }

        return $result;
    }

    /**
     * @return bool
     *
     * @throws LocalizedException
     */
    private function hasToRestrictByCustomerGroup(): bool
    {
        if (!$this->configProvider->isEnabled()) {
            return false;
        }

        $customerGroupId = $this->getCustomerGroupId();
        if ($customerGroupId === null) {
            return false;
        }

        $targetCustomerGroups = $this->configProvider->getTargetCustomerGroups();

        return in_array($customerGroupId, $targetCustomerGroups, true);
    }

    /**
     * @param array $result
     *
     * @return void
     *
     * @throws LocalizedException
     */
    private function restrictShippingMethods(&$result): void
    {
        $shippingMethodsToRestrict = $this->configProvider->getShippingMethodsToRestrict();

        $result = array_filter(
            $result,
            static function ($rate) use ($shippingMethodsToRestrict) {
                $rateCode = sprintf(
                    '%s_%s',
                    $rate->getData('carrier'),
                    $rate->getData('method')
                );

                return !in_array($rateCode, $shippingMethodsToRestrict, true);
            }
        );
    }

    /**
     * @return string|null
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getCustomerGroupId(): ?string
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        return (string)$this->customerSession->getCustomerGroupId();
    }
}
