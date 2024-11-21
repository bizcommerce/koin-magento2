<?php

namespace Koin\Payment\Controller\Payment;

use Koin\Payment\Helper\Data as HelperData;
use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class Status extends Action implements HttpGetActionInterface, CsrfAwareActionInterface
{
    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var Json */
    protected $json;

    /** @var HelperData */
    protected $helperData;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Json $json,
        HelperData $helperData,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->json = $json;
        $this->helperData = $helperData;
        $this->orderRepository = $orderRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        header('Content-Type: text/event-stream');
        header('Connection: keep-alive');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');

        $orderId = $this->getRequest()->getParam('oId');

        $count = 0;
        if ($orderId) {
            while (true) {
                $order = $this->orderRepository->get($orderId);
                $payment = $order->getPayment();
                $result = [
                    'order_id' => $order->getId(),
                    'order_status' => $order->getStatus(),
                    'payment_status' => $payment->getAdditionalInformation('status'),
                    'qr_code' => $payment->getAdditionalInformation('qr_code') ?? '',
                    'is_paid' => $payment->getAdditionalInformation('status') == Api::STATUS_COLLECTED
                ];

                echo "event: koin-pix\n" .
                    "data: " . json_encode($result) . "\n\n";
                ob_flush();
                flush();
                $count++;
                if ($count == 720) {
                    break;
                }
                sleep(5);
            }
        }

    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
