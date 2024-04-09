<?php

namespace Koin\Payment\Controller\Adminhtml\Installments\Rules;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassDelete
 * @package Biz\ErpIntegration\Controller\Adminhtml\Rule
 * @author Iago Cedran <iago@bizcommerce.com.br>
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $items = $this->getCollection();
        $collectionSize = $items->getSize();

        foreach ($items as $item) {
            try {
                $this->rulesRepository->delete($item);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
        return $this->getResultRedirect('*/*/');
    }
}
