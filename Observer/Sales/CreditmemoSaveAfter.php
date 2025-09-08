<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021 Koin
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Observer\Sales;

use Koin\Payment\Helper\Data;
use Koin\Payment\Service\NotificationService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;

class CreditmemoSaveAfter implements ObserverInterface
{
    /** @var Data  */
    protected $helper;

    /** @var NotificationService  */
    protected $notificationService;

    /**
     * @param Data $helper
     * @param NotificationService $notificationService
     */
    public function __construct(
        Data $helper,
        NotificationService $notificationService
    ) {
        $this->helper = $helper;
        $this->notificationService = $notificationService;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        // Only process new credit memos to avoid duplicate notifications
        if (!$creditmemo->getOrigData('entity_id')) {
            $order = $creditmemo->getOrder();

            try {
                $this->notificationService->sendNotificationForCreditmemo($order);
            } catch (\Exception $e) {
                $this->helper->log('Credit memo notification error: ' . $e->getMessage());
            }
        }
    }

}
