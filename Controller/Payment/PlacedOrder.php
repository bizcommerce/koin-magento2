<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021 Koin
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Controller\Payment;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * Tells the checkout whether the order of a quote was created.
 *
 * Used when the place order request is cut by a gateway timeout (e.g. nginx 504):
 * PHP keeps running and creates the order, but the browser never gets the response.
 */
class PlacedOrder implements HttpGetActionInterface
{
    public function __construct(
        private RequestInterface $request,
        private JsonFactory $resultJsonFactory,
        private CheckoutSession $checkoutSession,
        private CustomerSession $customerSession,
        private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        private OrderCollectionFactory $orderCollectionFactory
    ) {
    }

    public function execute(): JsonResult
    {
        $result = $this->resultJsonFactory->create();
        $result->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);

        $order = $this->getPlacedOrder((string) $this->request->getParam('quote_id'));
        if (!$order) {
            return $result->setData(['placed' => false]);
        }

        $this->checkoutSession->setLastQuoteId($order->getQuoteId())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());

        return $result->setData(['placed' => true]);
    }

    private function getPlacedOrder(string $cartId): ?Order
    {
        $quoteId = $this->resolveQuoteId($cartId);
        if (!$quoteId) {
            return null;
        }

        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('quote_id', $quoteId)
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(1);

        /** @var Order $order */
        $order = $collection->getFirstItem();
        if (!$order->getId() || $order->getPayment()->getMethod() !== \Koin\Payment\Model\Ui\CreditCard\ConfigProvider::CODE) {
            return null;
        }

        if ($this->customerSession->isLoggedIn()
            && (int) $order->getCustomerId() !== (int) $this->customerSession->getCustomerId()
        ) {
            return null;
        }

        return $order;
    }

    private function resolveQuoteId(string $cartId): int
    {
        if ($cartId === '') {
            return 0;
        }

        if ($this->customerSession->isLoggedIn()) {
            return (int) $cartId;
        }

        try {
            return $this->maskedQuoteIdToQuoteId->execute($cartId);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
