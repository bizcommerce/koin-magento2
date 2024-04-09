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

namespace Koin\Payment\Api\Data;

interface QueueInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const RESOURCE = 'resource';
    const RESOURCE_ID = 'resource_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set EntityId.
     * @param $entityId
     */
    public function setEntityId($entityId);

    /**
     * Get Resource.
     *
     * @return string
     */
    public function getResource();

    /**
     * Set Resource.
     * @param $resource
     */
    public function setResource($resource);

    /**
     * Get Resource ID.
     *
     * @return string
     */
    public function getResourceId();

    /**
     * Set Resource ID.
     * @param $resourceId
     */
    public function setResourceId($resourceId);

    /**
     * Get Status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Status.
     * @param $status
     */
    public function setStatus($status);

    /**
     * Get CreatedAt.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt.
     * @param $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * Get CreatedAt.
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set Updated At.
     * @param $updatedAt
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return \Koin\Payment\Api\Data\QueueExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \Koin\Payment\Api\Data\QueueExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(QueueExtensionInterface $extensionAttributes);
}
