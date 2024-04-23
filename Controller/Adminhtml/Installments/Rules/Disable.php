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

use Exception;
use Magento\Framework\Controller\Result\Redirect;
use Koin\Payment\Controller\Adminhtml\Installments\Rule;

/**
 * Class Delete
 * @package Biz\ProductLabels\Controller\Adminhtml\Rule
 */
class Disable extends Rule
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($ruleId = $this->getRequest()->getParam('id')) {
            try {
                $rule = $this->ruleRepository->getById($ruleId);
                $rule->setStatus(0);
                $this->ruleRepository->save($rule);
                $this->messageManager->addSuccessMessage(__('The Rule has been disabled.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('*/*/edit', ['id' => $ruleId]);
                return $resultRedirect;
            }
        } else {
            /** display error message */
            $this->messageManager->addErrorMessage(__('Rule was not found.'));
        }

        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
