<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Cron\Queue;

use Koin\Payment\Helper\Data;
use Koin\Payment\Helper\Antifraud as AntifraudHelper;
use Koin\Payment\Model\Antifraud as AntifraudModel;
use Koin\Payment\Model\Queue;
use Koin\Payment\Model\ResourceModel\Queue\CollectionFactory;
use Koin\Payment\Api\QueueRepositoryInterface;
use Koin\Payment\Api\AntifraudRepositoryInterface;
use Psr\Log\LoggerInterface;

class Antifraud
{
    /** @var Data  */
    protected $helper;

    /** @var AntifraudHelper  */
    protected $helperAntifraud;

    /** @var CollectionFactory  */
    protected $collectionFactory;

    /** @var QueueRepositoryInterface  */
    protected $queueRepository;

    /** @var AntifraudRepositoryInterface  */
    protected $antifraudRepository;

    /** @var LoggerInterface  */
    protected $logger;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param AntifraudHelper $helperAntifraud
     * @param QueueRepositoryInterface $queueRepository
     * @param AntifraudRepositoryInterface $antifraudRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $helper,
        AntifraudHelper $helperAntifraud,
        QueueRepositoryInterface $queueRepository,
        AntifraudRepositoryInterface $antifraudRepository,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->helperAntifraud = $helperAntifraud;
        $this->queueRepository = $queueRepository;
        $this->antifraudRepository = $antifraudRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $enable = $this->helper->getAntifraudConfig('active');
        if ($enable) {
            $collection = $this->collectionFactory->create();
            $collection
                ->addFieldToFilter('status', Queue::STATUS_PENDING)
                ->addFieldToFilter('resource', AntifraudModel::RESOURCE_CODE);

            /** @var Queue $queue */
            foreach ($collection as $queue) {
                try {
                    $this->helperAntifraud->sendAnalysis($queue);
                } catch (\Exception $e) {
                    $errorMessage = 'Error processing antifraud analysis: ' . $e->getMessage();
                    $this->logger->error($errorMessage, [
                        'queue_id' => $queue->getEntityId(),
                        'resource_id' => $queue->getResourceId(),
                        'exception' => $e
                    ]);

                    // Update queue status to error
                    try {
                        $queue->setStatus(Queue::STATUS_ERROR);
                        $this->queueRepository->save($queue);

                        // If resource is Antifraud, update the antifraud table too
                        if ($queue->getData('resource') === AntifraudModel::RESOURCE_CODE) {
                            try {
                                $antifraud = $this->antifraudRepository->get($queue->getResourceId());
                                $antifraud->setStatus('error');
                                $antifraud->setMessage($errorMessage);
                                $this->antifraudRepository->save($antifraud);
                            } catch (\Exception $antifraudException) {
                                $this->logger->error('Failed to update antifraud record: ' . $antifraudException->getMessage(), [
                                    'antifraud_id' => $queue->getResourceId(),
                                    'exception' => $antifraudException
                                ]);
                            }
                        }
                    } catch (\Exception $saveException) {
                        $this->logger->error('Failed to save error status: ' . $saveException->getMessage(), [
                            'queue_id' => $queue->getEntityId(),
                            'exception' => $saveException
                        ]);
                    }
                }
            }
        }
    }
}
