<?php

namespace Riversy\ShippingMethodRestrictions\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider
{
    private const CONFIG_PATH_ENABLED = 'enabled';
    private const CONFIG_PATH_SHIPPING_METHODS_TO_RESTRICT = 'shipping_methods_to_restrict';
    private const CONFIG_PATH_TARGET_CUSTOMER_GROUPS = 'target_customer_groups';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     *
     * @throws LocalizedException
     */
    public function isEnabled(): bool
    {
        return $this->getValue(self::CONFIG_PATH_ENABLED);
    }

    /**
     * @return array
     *
     * @throws LocalizedException
     */
    public function getShippingMethodsToRestrict()
    {
        return $this->getArrayValue(self::CONFIG_PATH_SHIPPING_METHODS_TO_RESTRICT);
    }

    /**
     * @return array
     *
     * @throws LocalizedException
     */
    public function getTargetCustomerGroups()
    {
        return $this->getArrayValue(self::CONFIG_PATH_TARGET_CUSTOMER_GROUPS);
    }

    /**
     * @param string $path
     *
     * @return array
     *
     * @throws LocalizedException
     */
    private function getArrayValue(string $path): array
    {
        return explode(
            ',',
            $this->getValue($path)
        );
    }

    /**
     * @param string $path
     *
     * @return null|int|string
     *
     * @throws LocalizedException
     */
    private function getValue(string $path)
    {
        $websiteId = $this->storeManager->getWebsite()->getId();

        return $this->scopeConfig->getValue(
            sprintf('shipping_method_restrictions/restrictions/%s', $path),
            'website',
            $websiteId
        );
    }
}
