<?php

/**
 * Biz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Biz.com license that is
 * available through the world-wide-web at this URL:
 * https://www.biz.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Biz
 * @package     Biz_ProductLabels
 * @copyright   Copyright (c) Biz (https://www.biz.com/)
 * @license     https://www.biz.com/LICENSE.txt
 */

namespace Koin\Payment\Controller\Adminhtml\Installments\Rules;

use Koin\Payment\Controller\Adminhtml\Installments\Rule;
use Koin\Payment\Helper\Data as HelperData;
use Koin\Payment\Model\InstallmentsRulesFactory;
use Koin\Payment\Model\ResourceModel\InstallmentsRulesRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 * @package Koin\Payment\Controller\Adminhtml\Rule
 */
class Edit extends Rule
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    public function __construct(
        Context $context,
        InstallmentsRulesRepository $ruleRepository,
        InstallmentsRulesFactory $rulesFactory,
        Registry $coreRegistry,
        HelperData $helperData,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context,
            $ruleRepository,
            $rulesFactory,
            $coreRegistry,
            $helperData
        );
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|Redirect|ResultInterface|Page
     * @throws LocalizedException
     */
    public function execute()
    {
        $ruleId = (int) $this->getRequest()->getParam('id');
        $rule = $this->initRule();
        if ($ruleId && !$rule->getId()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('koin/installments_rules/index');
            return $resultRedirect;
        }

        $data = $this->_session->getData('koin_installments_rule_data', true);
        if (!empty($data)) {
            $rule->setData($data);
        }
        $this->coreRegistry->register('koin_installments_rule', $rule);

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Koin_Payment::installments_rules');
        $resultPage->getConfig()->getTitle()->set(__('Manage Items'));

        $title = $rule->getId() ? $rule->getTitle() : __('Create New Item');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
