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

use Koin\Payment\Api\Data\AntifraudExtensionInterface;
use Koin\Payment\Api\Data\AntifraudInterface;
use Magento\Framework\Model\AbstractModel;

class Antifraud extends AbstractModel implements AntifraudInterface
{
    const RESOURCE_CODE = 'antifraud';

    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'koin_antifraud';

    /**
     * @var string
     */
    protected $_cacheTag = 'koin_antifraud';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'koin_antifraud';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Koin\Payment\Model\ResourceModel\Antifraud::class);
    }

    /**
     * @ingeritdoc
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @ingeritdoc
     */
    public function setIncrementId($incrementId)
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritDoc
     */
    public function getAntifraudId()
    {
        return $this->getData(self::ANTIFRAUD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAntifraudId($antifraudId)
    {
        $this->setData(self::ANTIFRAUD_ID, $antifraudId);
    }

    /**
     * @inheritDoc
     */
    public function getEvaluationId()
    {
        return $this->getData(self::EVALUATION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEvaluationId($evaluationId)
    {
        $this->setData(self::EVALUATION_ID, $evaluationId);
    }

    /**
     * @inheritDoc
     */
    public function getAnalysisType()
    {
        return $this->getData(self::ANALYSIS_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setAnalysisType($analysisType)
    {
        $this->setData(self::ANALYSIS_TYPE, $analysisType);
    }

    /**
     * @inheritDoc
     */
    public function getScore()
    {
        return $this->getData(self::SCORE);
    }

    /**
     * @inheritDoc
     */
    public function setScore($score)
    {
        $this->setData(self::SCORE, $score);
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
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage($message)
    {
         $this->getData(self::MESSAGE, $message);
    }

    /**
     * @inheritDoc
     */
    public function getSessionId()
    {
        return $this->getData(self::SESSION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSessionId($sessionId)
    {
        $this->setData(self::SESSION_ID, $sessionId);
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
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(AntifraudExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
