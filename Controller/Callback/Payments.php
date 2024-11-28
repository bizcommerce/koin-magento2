<?php

/**
 * @category    Koin
 * @package     Koin_Payment
 */

namespace Koin\Payment\Controller\Callback;

use Koin\Payment\Exception\OrderNotFinishedException;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Koin\Payment\Controller\Callback;
use Koin\Payment\Gateway\Http\Client\Payments\Api;
use Magento\Sales\Model\Order as SalesOrder;

class Payments extends Callback implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var string
     */
    protected $eventName = 'pix';

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        $hash = $request->getParam('hash');
        $storeHash = $this->helperData->getHash(0);
        return ($hash == $storeHash);
    }

    /**
     * Exemplo:
    {
        "status": {
            "type": "Collected", //Published, Collected, Refunded, Cancelled, Voided, Failed.
            "date": "2021-09-03T15:27:28.000Z"
        },
        "transaction": {
            "reference_id": "FGCCCEA53JYSHXX",
            "business_id": "6LVHR8W0V7BYEQX",
            "account": "Merchant1234",
            "amount": {
                "currency_code":"BRL",
                "value":1111.11,
                "breakdown":{
                    "cancelled_amount": {
                        "currency_code": "BRL",
                        "value": 100.00
                    }
                }
            },
        }
        "order_id": "7978c0c97ea847e78e8849634473c1f1",
        "refund_id":"9943a9f6-b6fc-4a2b-b004-8cd484e08cdd",
        "refund_amount":{
            "currency_code":"BRL",
            "value":500.0
        }
    }
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->helperData->log(__('Webhook %1', __CLASS__), self::LOG_NAME);

        $statusCode = 500;
        $method = '';
        $koinStatus = '';
        $content = [];

        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        try {
            $content = $this->getContent($this->getRequest());
            $params = $this->getRequest()->getParams();
            $this->logParams($content, $params);

            if (isset($content['transaction'])) {
                $transaction = $content['transaction'];
                if (isset($content['status'])) {
                    $koinStatus = $content['status']['type'] ?? $content['status'];
                    $koinState = $this->helperOrder->getStatus($koinStatus);
                    $order = $this->helperOrder->loadOrder($transaction['reference_id']);
                    $statusCode = 404;
                    if ($order->getId()) {
                        $method = $order->getPayment()->getMethod();
                        $amount = $this->getCallbackAmount($order, $content);
                        $this->helperOrder->updateOrder($order, $koinStatus, $koinState, $content, $amount, true);
                        $statusCode = 204;
                    }
                }
            }
        } catch (OrderNotFinishedException $e) {
            $statusCode = 409;
            $result->setHeader('Content-Type', 'application/json');
            $result->setContents(
                $this->helperData->jsonEncode([
                    'code' => $statusCode,
                    'error' => $e->getMessage()
                ])
            );
        } catch (\Exception $e) {
            $this->helperData->getLogger()->error($e->getMessage());
        } finally {
            $callBack = $this->callbackFactory->create();
            $callBack->setStatus($koinStatus);
            $callBack->setMethod($method);
            $callBack->setIncrementId($transaction['reference_id'] ?? '');
            $callBack->setPayload($this->json->serialize($content));
            $this->callbackResourceModel->save($callBack);
        }

        $result->setHttpResponseCode($statusCode);
        return $result;
    }

    /**
     * @param SalesOrder $order
     * @param array $content
     * @return float
     */
    protected function getCallbackAmount(SalesOrder $order, array $content): float
    {
        //Update Interest Rate
        $amount = $order->getBaseGrandTotal();
        if (isset($content['amount']) && isset($content['amount']['value'])) {
            $amount = $content['amount']['value'];
            //If there's breakdown amount
            try {
                if (isset($content['amount']['breakdown'])) {
                    $breakdown = $content['amount']['breakdown'];
                    $amount = isset($breakdown['cancelled_amount'])
                        ? $breakdown['cancelled_amount']['value']
                        : $breakdown['refunded_amount']['value'];
                }
            } catch (\Exception $e) {
                $this->helperData->log($e->getMessage());
            }
        }

        //If there's refund amount
        if (isset($content['refund_amount']) && isset($content['refund_amount']['value'])) {
            $amount = $content['refund_amount']['value'];
        }

        return (float) $amount;
    }
}
