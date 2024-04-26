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

namespace Koin\Payment\Controller\Request;

use Koin\Payment\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Save extends Action implements CsrfAwareActionInterface
{
    /** @var Data */
    protected $helperData;

    public function __construct(
        Context $context,
        Data $helperData
    ) {
        $this->helperData = $helperData;

        parent::__construct($context);
    }

    public function execute()
    {
        $statusCode = 500;
        try {
            $content = $this->helperData->jsonDecode($this->getRequest()->getContent());
            $this->helperData->saveRequest(
                $content['request'],
                $content['response'],
                $content['status_code'],
                $content['method']
            );
            $statusCode = 204;
        } catch (\Exception $e) {
            $this->helperData->log($e->getMessage());
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode($statusCode);
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
        $storeHash = $this->helperData->getHash(Data::REQUEST_SALT);
        return ($hash == $storeHash);
    }
}
