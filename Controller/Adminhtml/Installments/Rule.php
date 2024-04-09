<?php

namespace Koin\Payment\Controller\Adminhtml\Installments;

use Koin\Payment\Model\InstallmentsRules;
use Koin\Payment\Model\InstallmentsRulesFactory;
use Koin\Payment\Model\ResourceModel\InstallmentsRulesRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Koin\Payment\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

/**
 * Class Rule
 * @package Koin\Payment\Controller\Adminhtml\Installments
 */
abstract class Rule extends Action
{
    /** Authorization level of a basic admin session */
    public const ADMIN_RESOURCE = 'Koin_Payment::installments_rules';

    /**
     * @var InstallmentsRulesRepository
     */
    public $ruleRepository;

    /**
     * Rule model factory
     *
     * @var InstallmentsRulesFactory
     */
    public $rulesFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HelperData
     */
    protected $helperData;

    public function __construct(
        Context $context,
        InstallmentsRulesRepository $ruleRepository,
        InstallmentsRulesFactory $rulesFactory,
        Registry $coreRegistry,
        HelperData $helperData
    ) {
        $this->ruleRepository  = $ruleRepository;
        $this->rulesFactory = $rulesFactory;
        $this->coreRegistry = $coreRegistry;
        $this->helperData = $helperData;

        parent::__construct($context);
    }

    /**
     * @throws LocalizedException
     */
    protected function initRule(bool $register = false): InstallmentsRules
    {
        $rule = $this->rulesFactory->create();

        $ruleId = (int) $this->getRequest()->getParam('id');
        if ($ruleId) {
            $rule = $this->ruleRepository->getById($ruleId);
            if (!$rule->getId()) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
            }
        }

        if ($register) {
            $this->coreRegistry->register('koin_installments_rule', $rule);
        }

        return $rule;
    }
}
