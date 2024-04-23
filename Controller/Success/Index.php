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

namespace Koin\Payment\Controller\Success;

use Koin\Payment\Helper\Data as HelperData;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Model\OrderFactory;

class Index extends Action
{
    const LOG_NAME = 'koin-success';

    /**
     * @var string
     */
    protected $eventName = 'success';

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
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Event manager
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;


    /**
     * PostBack constructor.
     * @param Context $context
     * @param Json $json
     * @param ResultFactory $resultFactory
     * @param HelperData $helperData
     * @param ManagerInterface $eventManager
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        Json $json,
        ResultFactory $resultFactory,
        HelperData $helperData,
        ManagerInterface $eventManager,
        CheckoutSession $checkoutSession,
        OrderFactory $orderFactory
    ) {
        $this->json = $json;
        $this->resultFactory = $resultFactory;
        $this->helperData = $helperData;
        $this->eventManager = $eventManager;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;


        parent::__construct($context);
    }

    public function execute()
    {
        $this->helperData->log(__('Redirect %1', __CLASS__), self::LOG_NAME);

        try {
            $incrementId = $this->getRequest()->getParam('increment_id');
            if ($incrementId) {
                if (!$this->checkoutSession->getLastRealOrderId()) {
                    $order = $this->getOrderByIncrementId($incrementId);
                    $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                    $this->checkoutSession->setLastOrderId($order->getId());
                    $this->checkoutSession->setLastQuoteId($order->getQuoteId());
                    $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
                }
            }

        } catch (\Exception $e) {
            $this->helperData->getLogger()->critical($e->getMessage());
        }

        return $this->_redirect('checkout/onepage/success',  ['_secure' => true]);
    }

    /**
     * @return false|\Magento\Sales\Model\Order
     */
    protected function getOrderByIncrementId($incrementId)
    {
        if ($incrementId) {
            return $this->orderFactory->create()
                ->loadByIncrementId($incrementId);
        }

        return false;
    }

    /**
     * @param $result
     * @param $content
     * @param $params
     * @return mixed
     */
    public function dispatchEvent($result, $content, $params)
    {
        $this->eventManager->dispatch(
            'koin_notifications_' . $this->eventName,
            [
                'result' => $result,
                'content' => $content,
                'params' => $params
            ]
        );

        return $result;
    }

    /**
     * @param RequestInterface $request
     * @return mixed|string
     */
    protected function getContent(RequestInterface $request)
    {
        if (!$this->requestContent) {
            try {
                $content = $this->getRequest()->getContent();
                $this->requestContent = $this->json->unserialize($content);
            } catch (\Exception $e) {
                $this->helperData->getLogger()->critical($e->getMessage());
            }
        }
        return $this->requestContent;
    }


    /** * @inheritDoc */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

    /** * @inheritDoc */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        $hash = $request->getParam('hash');
        $storeHash = sha1($this->helperData->getGeneralConfig('app_key'));
        return ($hash == $storeHash);
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
