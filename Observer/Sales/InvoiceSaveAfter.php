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
use Magento\Sales\Model\Order\Invoice;

class InvoiceSaveAfter implements ObserverInterface
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
        /** @var Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();
        
        // Only process new invoices to avoid duplicate notifications
        if (!$invoice->getOrigData('entity_id')) {
            $order = $invoice->getOrder();
            
            try {
                $this->notificationService->sendNotificationForInvoice($order);
            } catch (\Exception $e) {
                $this->helper->log('Invoice notification error: ' . $e->getMessage());
            }
        }
    }

}