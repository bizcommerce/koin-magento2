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

namespace Koin\Payment\Helper;

use Koin\Payment\Api\AntifraudRepositoryInterface;
use Koin\Payment\Api\QueueRepositoryInterface;
use Koin\Payment\Gateway\Http\Client;
use Koin\Payment\Gateway\Http\Client\Risk\Api;
use Koin\Payment\Helper\Data as HelperData;
use Koin\Payment\Helper\Order as HelperOrder;
use Koin\Payment\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use Koin\Payment\Model\AntifraudFactory;
use Koin\Payment\Model\QueueFactory;
use Koin\Payment\Model\ResourceModel\Antifraud\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\OrderRepository;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Antifraud extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const APPROVED_STATUS = 'approved';
    public const REJECTED_STATUS = 'denied';
    public const DEFAULT_DOCUMENT_TYPE = 'DNI';
    public const DEFAULT_PRODUCT_TYPE = 'Generic';
    public const PAYMENT_METHOD_CREDIT_CARD = 'CreditCard';
    public const PAYMENT_METHOD_CASH = 'Cash';
    public const DEFAULT_TYPE = 'Ecommerce';

    /** @var QueueFactory  */
    protected $queueFactory;

    /** @var QueueRepositoryInterface  */
    protected $queueRepository;

    /** @var QueueCollectionFactory */
    protected $queueCollectionFactory;

    /** @var AntifraudFactory  */
    protected $antifraudFactory;

    /** @var CustomerSession  */
    protected $customerSession;

    /** @var AntifraudRepositoryInterface  */
    protected $antifraudRepository;

    /** @var CollectionFactory */
    protected $antifraudCollectionFactory;

    /** @var Json  */
    protected $json;

    /** @var HelperData */
    protected $helperData;

    /** @var HelperOrder */
    protected $helperOrder;

    /** @var OrderRepository */
    protected $orderRepository;

    /** @var Client */
    protected $client;

    /** @var Api */
    protected $api;

    /** @var ManagerInterface */
    private $eventManager;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     *  Koin Logging instance
     *
     * @var \Koin\Payment\Logger\Logger
     */
    protected $logger;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param QueueFactory $queueFactory
     * @param QueueRepositoryInterface $queueRepository
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param AntifraudFactory $antifraudFactory
     * @param AntifraudRepositoryInterface $antifraudRepository
     * @param CollectionFactory $antifraudCollectionFactory
     * @param CustomerSession $customerSession
     * @param CategoryRepositoryInterface $categoryRepository
     * @param OrderRepository $orderRepository
     * @param ManagerInterface $eventManager
     * @param Json $json
     * @param HelperData $helperData
     * @param HelperOrder $helperOrder
     * @param Client $client
     * @param Api $api
     * @param DateTime $dateTime
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        QueueFactory $queueFactory,
        QueueRepositoryInterface $queueRepository,
        QueueCollectionFactory $queueCollectionFactory,
        AntifraudFactory $antifraudFactory,
        AntifraudRepositoryInterface $antifraudRepository,
        CollectionFactory $antifraudCollectionFactory,
        CustomerSession $customerSession,
        CategoryRepositoryInterface $categoryRepository,
        OrderRepository $orderRepository,
        ManagerInterface $eventManager,
        Json $json,
        HelperData $helperData,
        HelperOrder $helperOrder,
        Client $client,
        Api $api,
        DateTime $dateTime,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);

        $this->queueFactory = $queueFactory;
        $this->queueRepository = $queueRepository;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->antifraudFactory = $antifraudFactory;
        $this->antifraudRepository = $antifraudRepository;
        $this->antifraudCollectionFactory = $antifraudCollectionFactory;
        $this->customerSession = $customerSession;
        $this->categoryRepository = $categoryRepository;
        $this->orderRepository = $orderRepository;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->helperData = $helperData;
        $this->helperOrder = $helperOrder;
        $this->client = $client;
        $this->api = $api;
        $this->dateTime = $dateTime;
        $this->encryptor = $encryptor;
    }

    /**
     * @param SalesOrder $order
     * @param $customerSessionId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addToQueue($order, $customerSessionId)
    {
        /** @var \Koin\Payment\Model\ResourceModel\Antifraud\Collection $collection */
        $collection = $this->antifraudCollectionFactory->create();
        $collection->addFieldToFilter('increment_id', $order->getIncrementId());
        if (!$collection->getSize()) {
            $antifraud = $this->antifraudFactory->create();
            $antifraud->setStatus(Api::STATUS_QUEUED);
            $antifraud->setIncrementId($order->getIncrementId());
            $antifraud->setSessionId($customerSessionId);
            $this->antifraudRepository->save($antifraud);

            if ($antifraud->getId()) {
                if (!$this->getQueue($antifraud->getId())) {
                    $queue = $this->queueFactory->create();
                    $queue->setResource(\Koin\Payment\Model\Antifraud::RESOURCE_CODE);
                    $queue->setResourceId($antifraud->getId());
                    $queue->setStatus(\Koin\Payment\Model\Queue::STATUS_PENDING);
                    $this->queueRepository->save($queue);
                }
            }
        }
    }

    /**
     * @param \Koin\Payment\Model\Queue $queue
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelQueue($queue)
    {
        if ($queue) {
            $queue->setStatus(\Koin\Payment\Model\Queue::STATUS_CANCELLED);
            $this->queueRepository->save($queue);
        }
    }

    /**
     * @param SalesOrder $order
     * @param $customerSessionId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeAntifraud(SalesOrder $order)
    {
        /** @var \Koin\Payment\Model\ResourceModel\Antifraud\Collection $collection */
        $collection = $this->antifraudCollectionFactory->create();
        $collection->addFieldToFilter('increment_id', $order->getIncrementId());

        if ($collection->getSize()) {
            /** @var \Koin\Payment\Model\Antifraud $antifraud */
            foreach ($collection as $antifraud) {
                //Search onlyt for pending queue
                $queue = $this->getQueue($antifraud->getId());
                if ($queue && $queue->getId()) {
                    $this->cancelQueue($queue);
                } else {
                    $evaluationId = $antifraud->getEvaluationId();
                    if ($antifraud->getStatus() == 'received') {
                        $requestPath = $this->helperData->getEndpointConfig('risk/cancel');
                        $request = __('DELETE: %s', str_replace('{evaluation_id}', $evaluationId, $requestPath));
                        $this->api->logRequest($request);
                        $response = $this->api->evaluation()->cancel($evaluationId);
                        $this->api->logResponse($response);
                        $this->api->saveRequest($request, $response['response'], $response['status']);

                        if ($response['status'] < 300) {
                            $antifraud->setStatus(Api::STATUS_ABORTED);
                            $this->antifraudRepository->save($antifraud);
                        }
                    } else {
                        $requestData = [
                            'type' => 'STATUS',
                            'sub_type' => 'CANCELLED',
                            'notification_date' => $this->dateTime->gmtDate('Y-m-d\TH:i:s') . '.000Z'
                        ];

                        $this->notify($evaluationId, $requestData, [], $order->getStoreId());
                    }
                }
            }
        }
    }

    public function notification(SalesOrder $order, $status = 'FINALIZED'): void
    {
        /** @var \Koin\Payment\Model\ResourceModel\Antifraud\Collection $collection */
        $collection = $this->antifraudCollectionFactory->create();
        $collection->addFieldToFilter('increment_id', $order->getIncrementId());

        if ($collection->getSize()) {
            /** @var \Koin\Payment\Model\Antifraud $antifraud */
            foreach ($collection as $antifraud) {
                $evaluationId = $antifraud->getEvaluationId();
                if ($antifraud->getStatus() != 'received') {
                    $requestData = [
                        'type' => 'STATUS',
                        'sub_type' => $status,
                        'notification_date' => $this->dateTime->gmtDate('Y-m-d\TH:i:s') . '.000Z'
                    ];

                    $this->notify($evaluationId, $requestData, [], $order->getStoreId());
                }
            }
        }
    }

    /**
     * @param mixed $evaluationId
     * @param array $requestData
     * @return void
     */
    public function notify(string $evaluationId, array $requestData, array $queryParams = [], $storeId = null): void
    {
        $urlPath = $this->api->evaluation()->getEndpointPath('risk/notifications', null, $evaluationId);
        $this->api->logRequest($urlPath);
        $this->api->logRequest($requestData);
        $response = $this->api->evaluation()->notification($evaluationId, $requestData, $queryParams);
        $this->api->logResponse($response);

        $urlLog = $urlPath;
        if (!empty($queryParams)) {
            $urlLog .= '?' . http_build_query($queryParams);
        }
        $body = $this->helperData->jsonEncode($requestData);
        $requestLog = "URL: {$urlLog} \n <br>BODY: {$body}";
        $this->api->saveRequest($requestLog, $response['response'], $response['status']);
    }

    /**
     * @param $resourceId
     * @param $status
     * @return false|\Koin\Payment\Model\Queue
     */
    public function getQueue($resourceId, $status = \Koin\Payment\Model\Queue::STATUS_PENDING)
    {
        $queueCollectionFactory = $this->queueCollectionFactory->create();
        $queueCollectionFactory->addFieldToFilter('resource', \Koin\Payment\Model\Antifraud::RESOURCE_CODE);
        $queueCollectionFactory->addFieldToFilter('resource_id', $resourceId);
        $queueCollectionFactory->addFieldToFilter('status', $status);

        if ($queueCollectionFactory->getSize()) {
            return $queueCollectionFactory->getFirstItem();
        }

        return false;
    }

    /**
     * @param $evaluationId
     * @return \Koin\Payment\Model\Antifraud|\Magento\Framework\DataObject
     */
    public function loadByEvaluationId($evaluationId)
    {
        /** @var \Koin\Payment\Model\ResourceModel\Antifraud\Collection $collection */
        $collection = $this->antifraudCollectionFactory->create();
        $collection->addFieldToFilter('evaluation_id', $evaluationId);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * @param $evaluationId
     * @param $status
     * @param $score
     * @param $analysisType
     * @return void
     */
    public function updateOrderByAnalysis($evaluationId, $status, $score, $analysisType)
    {
        /** @var \Koin\Payment\Model\Antifraud $antifraud */
        $antifraud = $this->loadByEvaluationId($evaluationId);
        if ($antifraud) {
            if ($score != $antifraud->getScore()) {
                /** @var SalesOrder $order */
                $order = $this->helperData->loadOrder($antifraud->getIncrementId());

                $this->updateAntifraud($antifraud, $status, $score, $analysisType);
                $this->updateOrder($order, $status, $score);
            }
        }
    }

    /**
     * @param \Koin\Payment\Model\Antifraud $antifraud
     * @param $status
     * @param $score
     * @param $analysisType
     * @param $riskId
     * @param $evaluationId
     * @param $message
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateAntifraud(
        $antifraud,
        $status,
        $score = null,
        $analysisType = null,
        $riskId = null,
        $evaluationId = null,
        $message = null
    ) {
        $antifraud->setStatus($status);
        $antifraud->setMessage($message);

        if ($riskId) {
            $antifraud->setAntifraudId($riskId);
        }
        if ($evaluationId) {
            $antifraud->setEvaluationId($evaluationId);
        }
        if ($score !== null) {
            $antifraud->setScore($score);
        }
        if ($analysisType) {
            $antifraud->setAnalysisType($analysisType);
        }

        $this->antifraudRepository->save($antifraud);
    }

    /**
     * Update Order Status
     *
     * @param SalesOrder $order
     * @param string $score
     * @param int $score
     */
    public function updateOrder($order, $status, $score)
    {
        try {
            if ($status == self::APPROVED_STATUS) {
                $changeStatusApproved = $this->helperData->getAntifraudConfig('change_status_approved');
                $approvedStatus = false;

                $captureApproved = $this->helperData->getAntifraudConfig('capture_approved_orders');
                if ($captureApproved && $order->canInvoice()) {
                    $this->helperOrder->captureOrder($order);
                }

                if ($changeStatusApproved) {
                    $approvedStatus = $this->helperData->getAntifraudConfig('approved_status');
                }

                $message = __('The order was approved by Fraud Analysis', $order->getIncrementId());
                $orderState = $this->helperOrder->getStatusState($approvedStatus);

                $order->addCommentToStatusHistory($message, $approvedStatus);
                $order->setState($orderState);
            } elseif ($status == self::REJECTED_STATUS) {
                $cancelDenied = $this->helperData->getAntifraudConfig('cancel_denied_orders');
                $changeStatusDenied = $this->helperData->getAntifraudConfig('change_status_denied');
                $deniedStatus = false;
                if ($cancelDenied) {
                    $deniedStatus = $this->helperData->getAntifraudConfig('denied_cancelled_status');
                    if ($order->canCancel()) {
                        $order->cancel();
                    } else {
                        $this->helperOrder->credimemoOrder($order);
                    }
                } elseif ($changeStatusDenied) {
                    $deniedStatus = $this->helperData->getAntifraudConfig('denied_status');
                }

                $orderState = $this->helperOrder->getStatusState($deniedStatus);
                $message = __('The order was repproved by Fraud Analysis', $order->getIncrementId());

                $order->addCommentToStatusHistory($message, $deniedStatus);
                $order->setState($orderState);

                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment = $order->getPayment();
                $payment->setIsFraudDetected(true);
                $this->helperOrder->savePayment($payment);
            }

            $order->setData('koin_antifraud_status', $status);
            $order->setData('koin_antifraud_score', $score);
            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->helperData->log($e->getMessage());
        }
    }

    /**
     * @param \Koin\Payment\Model\Queue $queue
     * @return void
     */
    public function sendAnalysis($queue): void
    {
        if ($queue->getResourceId()) {
            $queueStatus = \Koin\Payment\Model\Queue::STATUS_RUNNING;
            try {
                /** @var \Koin\Payment\Model\Antifraud $antifraud */
                $antifraud = $this->antifraudRepository->get($queue->getResourceId());
                if ($antifraud && $antifraud->getId()) {
                    $order = $this->helperOrder->loadOrder($antifraud->getIncrementId());
                    $orderData = [
                        'transaction' => [
                            'total_amount' => [
                                'currency_code' => $this->getOrderCurrencyCode($order),
                                'value' => (float) $order->getGrandTotal(),
                            ],
                            'reference_id' => $order->getIncrementId(),
                            'country_code' => $this->helperData->getDefaultCountryCode(),
                            'redirected' => false
                        ],
                        'buyer' => $this->getBuyerData($order),
                        'items' => $this->getOrderItems($order),
                        'payments' => $this->getPaymentData($order),
                        'type' => self::DEFAULT_TYPE,
                        'callback_url' => $this->helperData->getAntifraudCallbackUrl($order)
                    ];

                    //Admin order doesn't save the remote IP
                    $remoteIp = $order->getRemoteIp();
                    if ($remoteIp) {
                        $orderData['device'] = [
                            'session_id' => $antifraud->getSessionId()
                        ];

                        if (filter_var($remoteIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                            $orderData['device']['ipv4'] = $remoteIp;
                        } else {
                            $orderData['device']['ipv6'] = $remoteIp;
                        }
                    } else {
                        $orderData['transaction']['channel'] = 'backoffice';
                    }

                    $slaDate = $this->getSlaDate($order);
                    if ($slaDate) {
                        $orderData['sla_date'] = $slaDate;
                    }

                    $storeCode = trim($this->helperData->getGeneralConfig('store_code'));
                    if ($storeCode) {
                        $orderData['store']['code'] = $storeCode;
                    }

                    if (!$order->getIsVirtual()) {
                        $orderData['shipping'] = $this->getShippingData($order);
                    }

                    $this->api->logRequest($orderData);
                    $response = $this->api->evaluation()->sendData($orderData);
                    $this->api->logResponse($response);
                    $this->api->saveRequest($orderData, $response['response'], $response['status']);

                    $content = $response['response'] ?? null;
                    if ($content && $response['status'] < 300) {
                        $status = $content['status'] ?? null;
                        if ($status && isset($content['score'])) {
                            $score = $content['score'];
                            $analysisType = $content['analysis_type'] ?? null;
                            $antifraudId = $content['id'] ?? null;
                            $evaluationId = $content['evaluation_id'] ?? null;
                            $this->updateAntifraud($antifraud, $status, $score, $analysisType, $antifraudId, $evaluationId);
                            /** @var SalesOrder $order */
                            $order = $this->helperData->loadOrder($antifraud->getIncrementId());
                            $this->updateOrder($order, $status, $score);
                            $queueStatus = \Koin\Payment\Model\Queue::STATUS_DONE;
                        } elseif (isset($content['code'])) {
                            $queueStatus = \Koin\Payment\Model\Queue::STATUS_ERROR;
                            $this->saveErrorMessage($antifraud, $content);
                        }
                    } else {
                        $queueStatus = \Koin\Payment\Model\Queue::STATUS_ERROR;
                        $this->saveErrorMessage($antifraud, $content);
                    }
                }
            } catch (\Exception $e) {
                $queueStatus = \Koin\Payment\Model\Queue::STATUS_ERROR;
                if ($antifraud->getId()) {
                    $this->updateAntifraud(
                        $antifraud,
                        \Koin\Payment\Gateway\Http\Client\Risk\Api::STATUS_ERROR,
                        null,
                        null,
                        null,
                        null,
                        $e->getMessage()
                    );
                }
            }
            $queue->setStatus($queueStatus);
            $this->queueRepository->save($queue);
        }
    }

    /**
     * @param $order
     * @return string|null
     */
    public function getSlaDate($order)
    {
        $slaDate = null;
        try {
            $slaMinutes = (int) $this->helperData->getAntifraudConfig('sla_date');
            if ($slaMinutes > 0) {
                $increaseMinutes = "+{$slaMinutes} minutes";
                $timeStamp = $this->dateTime->timestamp($increaseMinutes);
                $slaDate = $this->dateTime->gmtDate('Y-m-d\TH:i:s', $timeStamp) . '.000Z';
            }

            $this->eventManager->dispatch(
                'koin_antifraud_sla_date',
                ['order' => $order, 'sla_date' => $slaDate]
            );
        } catch (\Exception $e) {
            $this->helperData->log($e->getMessage());
        }

        return $slaDate;
    }

    /**
     * @param $antifraud
     * @param $content
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveErrorMessage($antifraud, $content)
    {
        $errorStatus = \Koin\Payment\Gateway\Http\Client\Risk\Api::STATUS_ERROR;
        $errorMessage = ($content) ? $this->helperData->jsonEncode($content) : null;
        $this->updateAntifraud($antifraud, $errorStatus, null, null, null, null, $errorMessage);
    }

    /**
     * @return array
     */
    public function getAntifraudStatus()
    {
        return [
            Api::STATUS_DENIED,
            Api::STATUS_APPROVED,
            Api::STATUS_RECEIVED,
            Api::STATUS_QUEUED,
            Api::STATUS_ABORTED
        ];
    }

    /**
     * @param SalesOrder $order
     * @param bool $addressData
     * @return array
     */
    protected function getBuyerData(SalesOrder $order, bool $addressData = false): array
    {
        $data = [];

        $address = (!$addressData && $order->getShippingAddress())
            ? $order->getShippingAddress()
            : $order->getBillingAddress();
        $firstName = $order->getCustomerFirstname();
        $lastName = $order->getCustomerLastname();
        $fullName = $order->getCustomerName();
        $taxVat = $order->getCustomerTaxvat();

        if ($addressData) {
            $fullName = (string) $order->getPayment()->getCcOwner();
            if (!$fullName) {
                $firstName = $address->getFirstname() ?: $firstName;
                $lastName = $address->getLastname() ?: $lastName;
                $fullName = ($firstName && $lastName) ? $firstName . ' ' . $lastName : $order->getCustomerName();
            }

            if ($address->getVatId()) {
                $taxVat = $address->getVatId();
            }
            $taxVat = $order->getPayment()->getAdditionalInformation('koin_customer_taxvat') ?: $taxVat;
        }

        $documentType = self::DEFAULT_DOCUMENT_TYPE;
        if ($this->helperData->validateCnpj((string) $taxVat)) {
            $documentType = 'cnpj';
        } elseif ($this->helperData->validateCpf((string) $taxVat)) {
            $documentType = 'cpf';
        }

        $data['full_name'] = $fullName;
        $data['email'] = $order->getCustomerEmail();
        $data['address'] = $this->getAddressData($address);
        $data['phone'] = $this->getPhoneNumber($address);
        $data['document'] = [
            'number' => $this->helperData->clearNumber($taxVat),
            'type' => $documentType
        ];

        if (!$addressData) {
            $data['id'] = $order->getCustomerId() ?: $order->getRealOrderId();
            $data['first_name'] = $firstName;
            $data['last_name'] = $lastName;
        }

        return $data;
    }

    /**
     * @param SalesOrder $order
     * @return array
     */
    protected function getShippingData($order)
    {
        $address = $order->getShippingAddress();
        return [
            'address' => $this->getAddressData($address),
            'delivery' => [
                'by' => $order->getShippingDescription(),
                'date' => $this->getDeliveryDate($order),
                'type' => Data::DEFAULT_DELIVERY_TYPE
            ],
            'price' => [
                'currency_code' => $this->getOrderCurrencyCode($order),
                'value' => (float) $order->getShippingAmount()
            ]
        ];
    }

    /**
     * This method is public to allow plugins and to add a custom implementation, Magento usually doesn't have delivery date as default
     * @param $order
     * @return string
     */
    public function getDeliveryDate($order): string
    {
        return $this->helperData->getDeliveryDate($order);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return array
     */
    protected function getAddressData($address): array
    {
        return $this->helperData->getAddressData($address);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $address
     * @return array
     */
    protected function getPhoneNumber($address): array
    {
        $telephone = $this->helperData->digits($address->getTelephone());
        return [
            'area_code' => substr($telephone, 0, 2),
            'number' =>  substr($telephone, 2, strlen($telephone) - 1),
        ];
    }

    /**
     * @param SalesOrder $order
     * @return array
     */
    protected function getOrderItems($order): array
    {
        $products = [];
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $categoryId = $this->getProductCategoryId($orderItem->getProduct(), $orderItem->getProductId());
            $product = [
                'id' => $orderItem->getSku(),
                'name' => $orderItem->getName(),
                'category' => [
                    'id' => $categoryId,
                    'name' => $this->helperData->getCategoryName($categoryId) ?: $this->helperData->getStoreName()
                ],
                'price' => [
                    'currency_code' => $this->getOrderCurrencyCode($order),
                    'value' => (float) $orderItem->getPrice()
                ],
                'type' => self::DEFAULT_PRODUCT_TYPE,
                'quantity' => (float) $orderItem->getQtyOrdered()
            ];

            if ($orderItem->getDiscountAmount()) {
                $product['discount_amount'] = [
                    'currency_code' => $order->getOrderCurrencyCode(),
                    'value' => (float) $orderItem->getDiscountAmount()
                ];
            }

            $products[] = $product;
        }
        return $products;
    }

    public function getProductCategoryId($product, $defaultValue): int
    {
        $categoryId = $defaultValue;
        $categoryIds = $product->getCategoryIds();
        if (!empty($categoryIds)) {
            $categoryId = $categoryIds[0];
        }
        return (int) $categoryId;
    }

    /**
     * @param SalesOrder $order
     * @return string
     */
    public function getOrderCurrencyCode($order): string
    {
        return $this->helperData->getOrderCurrencyCode($order);
    }

    /**
     * @param SalesOrder $order
     * @return array
     */
    public function getPaymentData($order)
    {
        $payments = [];

        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $paymentModel */
        $paymentModel = $order->getPayment();
        $method = $paymentModel->getCcLast4() ? self::PAYMENT_METHOD_CREDIT_CARD : self::PAYMENT_METHOD_CASH;
        $transactionId = $this->getTransactionId($paymentModel);
        $payment = [
            'id' => $transactionId,
            'amount' => [
                'currency_code' => $this->getOrderCurrencyCode($order),
                'value' => (float) $order->getGrandTotal()
            ],
            'method' => $method,
            'payer' => $this->getBuyerData($order, true)
        ];

        if ($method == self::PAYMENT_METHOD_CREDIT_CARD) {
            $payment['installments'] = $this->getInstallments($order, $paymentModel);

            $payment['details'] = [
                'bin' => $this->getCcBin($paymentModel),
                'brand_name' => $paymentModel->getCcType(),
                'expiration_month' => $paymentModel->getCcExpMonth(),
                'expiration_year' => $paymentModel->getCcExpYear(),
                'last_digits' => $paymentModel->getCcLast4()
            ];
        }

        $payments[] = $payment;
        return $payments;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $paymentModel
     * @return string
     */
    protected function getTransactionId($paymentModel)
    {
        //Added this because MestreMage Cielo module don't save the transaction id as it should be
        $transactionId = $paymentModel->getAdditionalInformation('cielo_PaymentId');
        if (!$transactionId) {
            $transactionId = $paymentModel->getTransactionId();
            if (!$transactionId) {
                $transactionId = $paymentModel->getCcTransId();
                if (!$transactionId) {
                    $transactionId = $paymentModel->getLastTransId();
                    if (!$transactionId) {
                        $transactionId = $paymentModel->getEntityId();
                    }
                }
            }
        }
        return $transactionId;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $paymentModel
     * @return false|string|null
     */
    public function getCcBin($paymentModel)
    {
        $ccBin = null;
        if ($paymentModel->getCcNumberEnc()) {
            $ccBin = substr($this->encryptor->decrypt($paymentModel->getCcNumberEnc()), 0, 6);
        } else {
            if ($paymentModel->getAdditionalInformation('cc_bin')) {
                $ccBin = $paymentModel->getAdditionalInformation('cc_bin');
            } elseif ($paymentModel->getAdditionalInformation('cc_number')) {
                $ccBin = substr((string) $paymentModel->getAdditionalInformation('cc_number'), 0, 6);
            } elseif ($paymentModel->getAdditionalInformation('card_first_digits')) {
                $ccBin = $paymentModel->getAdditionalInformation('card_first_digits');
            }
        }

        return (is_numeric($ccBin) && strlen($ccBin) == 6) ? $ccBin : null;
    }


    /**
     * It'll try to get installments in many ways, otherwise, will send 1
     * @param SalesOrder $order
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $paymentModel
     * @return int
     */
    public function getInstallments($order, $paymentModel)
    {
        $installments = 0;
        try {
            $installments = ((int)$paymentModel->getAdditionalInformation('installments') > 0)
                ? (int)$paymentModel->getAdditionalInformation('installments')
                : (int)$paymentModel->getAdditionalInformation('cc_installments');
            if (!$installments) {
                //PagSeguro Ricardo Martins
                $installments = (int)$paymentModel->getAdditionalInformation('installment_quantity');
                if (!$installments) {
                    //PayPalBR
                    $installments = (int)$paymentModel->getAdditionalInformation('term');
                    if (!$installments) {
                        //Order Fields
                        $installments = (int) $order->getData('installments') ?: (int) $order->getData('cc_installments');
                        if (!$installments) {
                            /** @var array $additionalData */
                            $additionalData = $this->json->unserialize($paymentModel->getAdditionalData());
                            if (isset($additionalData['installments'])) {
                                $installments = (int)$additionalData['installments'];
                                if (!$installments && isset($additionalData['cc_installments'])) {
                                    $installments = (int)$additionalData['cc_installments'];
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helperData->log($e->getMessage());
        }

        return $installments > 0 ? $installments : 1;
    }

}
