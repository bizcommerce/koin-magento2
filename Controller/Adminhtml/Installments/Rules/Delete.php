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
class Delete extends Rule
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->ruleRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The Rule has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('*/*/edit', ['id' => $id]);
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
