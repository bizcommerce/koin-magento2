<?php

namespace Koin\Payment\Controller\Adminhtml\Installments\Rules;

use Koin\Payment\Model\ResourceModel\InstallmentsRules\CollectionFactory;
use Koin\Payment\Model\ResourceModel\InstallmentsRulesRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractMassAction extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var InstallmentsRulesRepository
     */
    protected $rulesRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        InstallmentsRulesRepository $rulesRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->rulesRepository = $rulesRepository;
        parent::__construct($context);
    }

    /**
     * @throws LocalizedException
     */
    public function getCollection(): AbstractDb
    {
        return $this->filter->getCollection($this->collectionFactory->create());
    }

    protected function getResultRedirect(string $path): ResultRedirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($path);
    }
}
