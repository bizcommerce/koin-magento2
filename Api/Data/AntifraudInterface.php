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

interface AntifraudInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const INCREMENT_ID = 'increment_id';
    const ANTIFRAUD_ID = 'antifraud_id';
    const EVALUATION_ID = 'evaluation_id';
    const ANALYSIS_TYPE = 'analysis_type';
    const SCORE = 'score';
    const MESSAGE = 'message';
    const STATUS = 'status';
    const SESSION_ID = 'session_id';
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
     * Get IncrementID.
     *
     * @return string
     */
    public function getIncrementId();

    /**
     * Set IncrementId.
     * @param $orderId
     */
    public function setIncrementId($incrementId);

    /**
     * Get Method.
     *
     * @return string
     */
    public function getAntifraudId();

    /**
     * Set antifraud_id.
     * @param $antifraudId
     */
    public function setAntifraudId($antifraudId);

    /**
     * Get EvaluationId.
     *
     * @return string
     */
    public function getEvaluationId();

    /**
     * Set EvaluationId.
     * @param $evaluationId
     */
    public function setEvaluationId($evaluationId);

    /**
     * Get Status.
     *
     * @return string
     */
    public function getAnalysisType();

    /**
     * Set analysisType.
     * @param $analysisType
     */
    public function setAnalysisType($analysisType);

    /**
     * Get Score.
     *
     * @return string
     */
    public function getScore();

    /**
     * Set Score.
     * @param $score
     */
    public function setScore($score);

    /**
     * Get Status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set Message.
     * @param $message
     */
    public function setMessage($message);

    /**
     * Get Message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set Status.
     * @param $status
     */
    public function setStatus($status);

    /**
     * Get Session ID.
     *
     * @return string
     */
    public function getSessionId();

    /**
     * Set Session ID.
     * @param $sessionId
     */
    public function setSessionId($sessionId);

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
     * @return \Koin\Payment\Api\Data\AntifraudExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * @param \Koin\Payment\Api\Data\AntifraudExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(AntifraudExtensionInterface $extensionAttributes);
}
