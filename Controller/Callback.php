<?php

/**
 * @category    Koin
 * @package     Koin_Payment
 */

namespace Koin\Payment\Controller;

use Koin\Payment\Helper\Data as HelperData;
use Koin\Payment\Helper\Antifraud as HelperAntifraud;
use Koin\Payment\Helper\Order as HelperOrder;
use Koin\Payment\Model\CallbackFactory;
use Koin\Payment\Model\ResourceModel\Callback as CallbackResourceModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

abstract class Callback extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    const LOG_NAME = 'koin-callback';

    /**
     * @var string
     */
    protected $eventName = 'redirect';

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperAntifraud
     */
    protected $helperAntifraud;

    /**
     * @var HelperOrder
     */
    protected $helperOrder;

    /**
     * @var CallbackResourceModel
     */
    protected $callbackResourceModel;

    /**
     * @var CallbackFactory
     */
    protected $callbackFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var string
     */
    protected $requestContent = '';

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

    public function __construct(
        Context $context,
        Json $json,
        ResultFactory $resultFactory,
        HelperData $helperData,
        HelperAntifraud $helperAntifraud,
        HelperOrder $helperOrder,
        CallbackResourceModel $callbackResourceModel,
        CallbackFactory $callbackFactory,
        ManagerInterface $eventManager
    ) {
        $this->json = $json;
        $this->resultFactory = $resultFactory;
        $this->helperData = $helperData;
        $this->helperAntifraud = $helperAntifraud;
        $this->helperOrder = $helperOrder;
        $this->callbackResourceModel = $callbackResourceModel;
        $this->callbackFactory = $callbackFactory;
        $this->eventManager = $eventManager;

        parent::__construct($context);
    }

    /**
     * https://api-docs.koin.com.br/reference/webhook
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    abstract public function execute();

    /**
     * @param $result
     * @param $content
     * @param $params
     * @return mixed
     */
    public function dispatchEvent($result, $content, $params)
    {
        $this->eventManager->dispatch(
            'koin_callback_' . $this->eventName,
            [
                'result' => $result,
                'content' => $content,
                'params' => $params
            ]
        );

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

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
     * @param RequestInterface $request
     * @return mixed|string
     */
    protected function getContent(RequestInterface $request)
    {
        if (!$this->requestContent) {
            try {
                $content = $request->getContent() ?: $this->getRawBody();
                if ($content) {
                    $this->requestContent = $this->json->unserialize($content);
                }
            } catch (\Exception $e) {
                $this->helperData->getLogger()->critical($e->getMessage());
            }
        }
        return $this->requestContent;
    }

    protected function getRawBody()
    {
        $requestBody = file_get_contents('php://input');
        if (strlen($requestBody) > 0) {
            return $requestBody;
        }
        return '';
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
