<?php

namespace Riversy\ShippingMethodRestrictions\Plugin\Model\Rate\Result;

use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Model\Rate\Result;
use Riversy\ShippingMethodRestrictions\Model\ConfigProvider;
use Riversy\ShippingMethodRestrictions\Model\CustomerGroupProvider;

class AfterGetAllRatesPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerGroupProvider
     */
    private $customerGroupProvider;

    /**
     * @param ConfigProvider $configProvider
     * @param CustomerGroupProvider $customerGroupProvider
     */
    public function __construct(
        ConfigProvider $configProvider,
        CustomerGroupProvider $customerGroupProvider
    ) {
        $this->configProvider = $configProvider;
        $this->customerGroupProvider = $customerGroupProvider;
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

        $customerGroupId = $this->customerGroupProvider->getCustomerGroupId();
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
    private function restrictShippingMethods(array &$result): void
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
}
