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

namespace Koin\Payment\Model\ResourceModel;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class InstallmentsRules extends AbstractDb
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /** @var ResolverInterface */
    protected $localeResolver;

    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        $resourcePrefix = null
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('koin_installments_rules', 'entity_id');
    }

    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getStartDate()) {
            $object->setStartDate($this->formatDate($object->getStartDate()));
        }
        if ($object->getEndDate()) {
            $object->setEndDate($this->formatDate($object->getEndDate()));
        }
        return parent::_beforeSave($object);
    }

    private function formatDate(string $localizedDate): string
    {
        return date('Y-m-d', (new \IntlDateFormatter(
            $this->localeResolver->getLocale(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        ))->parse($localizedDate));
    }
}
