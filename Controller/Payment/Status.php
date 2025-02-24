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
        // Set headers for Server-Sent Events
        $orderId = $this->getRequest()->getParam('oId');
        $hash = hash('sha512', $orderId);

        $count = 0;
        if ($orderId) {
            while (true) {
                $response = $this->getResponse();
                $response->setHeader('Content-Type', 'text/event-stream', true);
                $response->setHeader('Connection', 'keep-alive', true);
                $response->setHeader('Cache-Control', 'no-cache', true);
                $response->setHeader('X-Accel-Buffering', 'no', true);

                $this->orderRepository->_resetState();
                $order = $this->orderRepository->get($orderId);
                $payment = $order->getPayment();
                $result = [
                    'order_id' => $order->getId(),
                    'order_status' => $order->getStatus(),
                    'payment_status' => $payment->getAdditionalInformation('status'),
                    'qr_code' => $payment->getAdditionalInformation('qr_code') ?? '',
                    'hash' => $hash,
                    'is_paid' => $payment->getAdditionalInformation('status') == Api::STATUS_COLLECTED
                ];

                $data =  "event: koin-payment-status\n" .
                    "data: " . json_encode($result) . "\n\n";
                $response->setBody($data);
                $response->sendResponse();

                ob_flush();
                flush();
                $count++;
                if ($count == 720) {
                    break;
                }
                sleep(5);
                $response->_resetState();
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
