<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Cron\Queue;

use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Antifraud as AntifraudHelper;
use Koin\Payment\Model\ResourceModel\Queue\CollectionFactory;

class Antifraud
{
    /** @var Data  */
    protected $helper;

    /** @var AntifraudHelper  */
    protected $helperAntifraud;

    /** @var CollectionFactory  */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param AntifraudHelper $helperAntifraud
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $helper,
        AntifraudHelper $helperAntifraud
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->helperAntifraud = $helperAntifraud;
    }

    public function execute()
    {
        $enable = $this->helper->getAntifraudConfig('active');
        if ($enable) {
            $collection = $this->collectionFactory->create();
            $collection
                ->addFieldToFilter('status', \Koin\Payment\Model\Queue::STATUS_PENDING)
                ->addFieldToFilter('resource', \Koin\Payment\Model\Antifraud::RESOURCE_CODE);

            /** @var \Koin\Payment\Model\Queue $queue */
            foreach ($collection as $queue) {
                $this->helperAntifraud->sendAnalysis($queue);
            }
        }
    }
}
