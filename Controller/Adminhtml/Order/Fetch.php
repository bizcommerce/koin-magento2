<?php

namespace Koin\Payment\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Fetch extends Action implements HttpGetActionInterface
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    public function execute(): ResponseInterface
    {
        $orderId = $this->getRequest()->getParam('order_id');
        try {
            $order = $this->orderRepository->get($orderId);
            $payment = $order->getPayment();
            $payment->update();

            $this->messageManager->addSuccessMessage(__('Transaction information fetched successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error fetching transaction information: %1', $e->getMessage()));
        }

        return $this->_redirect('sales/order/view', ['order_id' => $orderId]);
    }
}
