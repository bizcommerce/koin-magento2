<?php

/**
 *
 *
 *
 * @category    Koin
 * @package     Koin_Payment
 */

namespace Koin\Payment\Controller\Installments;

use Koin\Payment\Helper\Data as HelperData;
use Koin\Payment\Helper\Installments;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

class Retrieve extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /** @var HelperData */
    protected $helperData;

    /** @var Json */
    protected $json;

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var Session */
    protected $checkoutSession;

    /** @var Installments */
    private $helperInstallments;

    /** @var SessionManagerInterface */
    protected $session;

    public function __construct(
        Context $context,
        Json $json,
        Session $checkoutSession,
        SessionManagerInterface $session,
        JsonFactory $resultJsonFactory,
        Installments $helperInstallments,
        HelperData $helperData
    ) {
        $this->json = $json;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        $this->helperInstallments = $helperInstallments;

        return parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $result->setHttpResponseCode(401);

        try{
            $this->checkoutSession->unsetData('koin_installments');
            $content = $this->getRequest()->getContent();
            $bodyParams = ($content) ? $this->json->unserialize($content) : [];
            $ccNumber = $bodyParams['cc_number'] ?? '';

            $result->setJsonData($this->json->serialize($this->getInstallments($ccNumber)));
            $result->setHttpResponseCode(200);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(500);
        }

        return $result;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getInstallments(string $ccNumber): array
    {
        //Saved to use in Quote Interest Totals
        $this->session->setKoinCcNumber($ccNumber);

        $grandTotal = $this->checkoutSession->getQuote()->getGrandTotal();
        $interestRate = (float) $this->checkoutSession->getQuote()->getKoinInterestAmount();
        $storeId = $this->checkoutSession->getQuote()->getStoreId();

        $price = $grandTotal - $interestRate;
        return $this->helperInstallments->getAllInstallments($price, $ccNumber, $storeId);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
