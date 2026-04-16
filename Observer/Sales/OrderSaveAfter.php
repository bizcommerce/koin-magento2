<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021 Koin
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Helper\Antifraud;
use Koin\Payment\Helper\Data;
use Koin\Payment\Service\NotificationService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderSaveAfter implements ObserverInterface
{
    private static array $processingOrders = [];

    public function __construct(
        private Data $helper,
        private Antifraud $helperAntifraud,
        private NotificationService $notificationService
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getDataObject();

        $orderId = $order->getId();
        if (!$orderId || isset(self::$processingOrders[$orderId])) {
            return;
        }

        if (!$order->getOrigData('entity_id')) {
            return;
        }

        self::$processingOrders[$orderId] = true;

        try {
            $originalState = $order->getOrigData('state');
            if ($originalState != $order->getState()) {
                $this->notificationService->sendNotificationForOrderState($order, true);
            }

            if ($this->helperAntifraud->isEligibleForAnalysis($order)) {
                $this->helperAntifraud->sendAnalysis($order);
            }
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        } finally {
            unset(self::$processingOrders[$orderId]);
        }
    }
}
