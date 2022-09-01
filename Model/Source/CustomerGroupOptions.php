<?php

namespace Riversy\ShippingMethodRestrictions\Model\Source;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;

class CustomerGroupOptions implements OptionSourceInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var DataObject
     */
    private $dataObjectConverter;

    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        DataObject $dataObjectConverter
    ) {
        $this->groupRepository = $groupRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     *
     * @throws LocalizedException
     */
    public function toOptionArray()
    {
        $emptyCriteria = $this->criteriaBuilder->create();
        $customerGroups = $this->groupRepository
            ->getList($emptyCriteria)
            ->getItems();

        return $this->dataObjectConverter->toOptionArray($customerGroups, 'id', 'code');
    }
}
