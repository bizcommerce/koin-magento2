<?php

namespace Koin\Payment\Controller\Adminhtml\Installments\Rules;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassStatus
 * @package Biz\ErpIntegration\Controller\Adminhtml\Category
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class MassStatus extends AbstractMassAction
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $updated = 0;

        $items = $this->getCollection();
        $status = (int) $this->getRequest()->getParam('status');

        foreach ($items as $item) {
            try {
                $item->setStatus($status);
                $this->rulesRepository->save($item);
                $updated++;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $updated));

        return $this->getResultRedirect('*/*/');
    }
}
