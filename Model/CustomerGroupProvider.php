<?php

namespace Riversy\ShippingMethodRestrictions\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Area;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerGroupProvider
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var State
     */
    private $state;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param State $state
     * @param RequestInterface $request
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        State $state,
        RequestInterface $request
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->state = $state;
        $this->request = $request;
    }

    /**
     * @return string|null
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerGroupId(): ?string
    {
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            return $this->getAdminhtmlCustomerGroupId();
        } else {
            return $this->getSessionCustomerGroupId();
        }
    }

    /**
     * @return string|null
     */
    private function getAdminhtmlCustomerGroupId(): ?string
    {
        $customerId = $this->request->getParam('customer_id');
        if (!$customerId) {
            return null;
        }

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (Exception $e) {
            return null;
        }

        return (string)$customer->getGroupId();
    }

    /**
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getSessionCustomerGroupId(): ?string
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        return (string)$this->customerSession->getCustomerGroupId();
    }
}
