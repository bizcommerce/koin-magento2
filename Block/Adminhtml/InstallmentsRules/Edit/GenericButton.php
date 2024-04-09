<?php

namespace Koin\Payment\Block\Adminhtml\InstallmentsRules\Edit;

use Magento\Backend\Block\Widget\Context;
use Koin\Payment\Api\InstallmentsRulesRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var InstallmentsRulesRepositoryInterface
     */
    protected $rulesRepository;

    /**
     * @param Context $context
     * @param InstallmentsRulesRepositoryInterface $rulesRepository
     */
    public function __construct(
        Context $context,
        InstallmentsRulesRepositoryInterface $rulesRepository
    ) {
        $this->context = $context;
        $this->rulesRepository = $rulesRepository;
    }

    public function getEntityId(): int
    {
        try {
            $ruleId = $this->context->getRequest()->getParam('id');
            if ($ruleId) {
                return $this->rulesRepository->getById($ruleId)->getId();
            }
        } catch (NoSuchEntityException $e) {
        }
        return 0;
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
