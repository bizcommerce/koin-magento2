<?php

namespace Koin\Payment\Model\Config\Source\Customer;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class Groups implements OptionSourceInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => __('...')]
        ];
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $groups = $this->groupRepository->getList($searchCriteria)->getItems();

        foreach ($groups as $group) {
            $options[] = [
                'value' => $group->getId(),
                'label' => $group->getCode()
            ];
        }

        return $options;
    }
}
