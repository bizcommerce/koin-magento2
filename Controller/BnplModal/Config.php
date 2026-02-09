<?php
declare(strict_types=1);
/**
 *
 *
 *
 * @category    Koin
 * @package     Koin_Payment
 */

namespace Koin\Payment\Controller\BnplModal;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Koin\Payment\Helper\Data;
use Psr\Log\LoggerInterface;

class Config implements HttpGetActionInterface
{
    public function __construct(
        private readonly JsonFactory      $resultJsonFactory,
        private readonly RequestInterface $request,
        private readonly Data             $helper,
        private readonly LoggerInterface  $logger,
        private readonly FormKeyValidator $formKeyValidator
    )
    {
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = [
            'success' => false,
            'message' => '',
            'config' => []
        ];

        try {
            if (!$this->formKeyValidator->validate($this->request)) {
                $data['message'] = __('Invalid form key.');
                return $result->setData($data);
            }

            $data['success'] = true;
            $data['config'] = [
                'installments' => $this->getInstallmentConfig(),
                'storeName' => $this->getStoreNameConfig(),
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $data['message'] = __('An error occurred while loading configuration.');
            return $result->setData($data);
        }

        return $result->setData($data);
    }

    private function getInstallmentConfig(): string
    {
        return trim((string)$this->helper->getConfig('payment_error_modal_installment'));
    }

    private function getStoreNameConfig(): string
    {
        return trim((string)$this->helper->getConfig('payment_error_modal_store_name'));
    }
}
