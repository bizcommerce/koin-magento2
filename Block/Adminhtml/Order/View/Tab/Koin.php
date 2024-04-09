<?php

/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Block\Adminhtml\Order\View\Tab;

use Koin\Payment\Helper\Data;
use Koin\Payment\Model\ResourceModel\Callback\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;

class Koin extends Template implements TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Koin_Payment::order/view/tab/koin.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /** @var CollectionFactory */
    protected $callbackCollectionFactory;

    /** @var Data */
    protected $helper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $callbackFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $callbackFactory,
        Data $helper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->callbackCollectionFactory = $callbackFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Koin');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Koin');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Only if payment method is Koin
     * @inheritdoc
     */
    public function canShowTab()
    {
        if ($this->_authorization->isAllowed('Koin_Payment::callbacks')) {
            $method = $this->getOrder()->getPayment()->getMethod();
            if (in_array($method, $this->helper->getAllowedMethods())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return \Koin\Payment\Model\ResourceModel\Callback\Collection
     */
    public function getCallbackCollection()
    {
        $callbackCollection = $this->callbackCollectionFactory->create();
        $callbackCollection->addFieldToFilter('increment_id', $this->getOrder()->getIncrementId());
        return $callbackCollection;
    }
}
