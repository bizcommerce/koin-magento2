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

namespace Koin\Payment\Model;

use Koin\Payment\Api\Data\QueueExtensionInterface;
use Koin\Payment\Api\Data\QueueInterface;
use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel implements QueueInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ERROR = 'error';
    const STATUS_RUNNING = 'running';
    const STATUS_DONE = 'done';

    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'koin_queue';

    /**
     * @var string
     */
    protected $_cacheTag = 'koin_queue';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'koin_queue';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Koin\Payment\Model\ResourceModel\Queue::class);
    }

    /**
     * @inheritDoc
     */
    public function getResource()
    {
        return $this->getData(self::RESOURCE);
    }

    /**
     * @inheritDoc
     */
    public function setResource($resource)
    {
        $this->setData(self::RESOURCE, $resource);
    }

    /**
     * @ingeritdoc
     */
    public function getResourceId()
    {
        return $this->getData(self::RESOURCE_ID);
    }

    /**
     * @ingeritdoc
     */
    public function setResourceId($resourceId)
    {
        $this->setData(self::RESOURCE_ID, $resourceId);
    }

    /**
     * @ingeritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @ingeritdoc
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @ingeritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @ingeritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @ingeritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @ingeritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        //@phpstan-ignore-next-line
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(QueueExtensionInterface $extensionAttributes)
    {
        //@phpstan-ignore-next-line
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
