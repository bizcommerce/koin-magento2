<?php

/**
 *
 *
 *
 *
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Koin
 * @package     Koin_Payment
 *
 *
 */

namespace Koin\Payment\Controller\Redirect;

use Koin\Payment\Helper\Data as HelperData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

class Index extends Action
{
    const LOG_NAME = 'koin-redirect';

    /**
     * @var string
     */
    protected $eventName = 'redirect';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var string
     */
    protected $requestContent;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * Event manager
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * PostBack constructor.
     * @param Context $context
     * @param Json $json
     * @param HelperData $helperData
     * @param Api $api
     * @param ManagerInterface $eventManager
     * @param OrderFactory $orderFactory
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        Json $json,
        HelperData $helperData,
        ManagerInterface $eventManager,
        OrderFactory $orderFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->json = $json;
        $this->helperData = $helperData;
        $this->eventManager = $eventManager;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $this->helperData->log(__('Redirect %1', __CLASS__), self::LOG_NAME);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $lastOrder = $this->getLastOrder();

        if (!$lastOrder) {
            throw new NotFoundException(__('Order not found'));
        }

        $returnUrl = $lastOrder->getPayment()->getAdditionalInformation('return_url');
        if ($lastOrder && $returnUrl) {
            if ($this->helperData->getGeneralConfig('use_sandbox')) {
                $resultRedirect->setHeader('XDESP-SANDBOX', true);
            }

            $resultRedirect->setUrl($returnUrl);
        }

        return $resultRedirect;
    }

    /**
     * @return false|\Magento\Sales\Model\Order
     */
    protected function getLastOrder()
    {
        if ($this->checkoutSession->getLastRealOrderId()) {
            return $this->orderFactory->create()
                ->loadByIncrementId($this->checkoutSession->getLastRealOrderId());
        }

        return false;
    }

    /**
     * @param $content
     * @param $params
     */
    protected function logParams($content, $params)
    {
        $this->helperData->log(__('Content'), self::LOG_NAME);
        $this->helperData->log($content, self::LOG_NAME);

        $this->helperData->log(__('Params'), self::LOG_NAME);
        $this->helperData->log($params, self::LOG_NAME);
    }
}
