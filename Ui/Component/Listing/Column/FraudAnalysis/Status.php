<?php
/**
 * @package Koin\Payment
 * @copyright Copyright (c) 2021 Koin
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Koin\Payment\Ui\Component\Listing\Column\FraudAnalysis;

use Koin\Payment\Helper\Antifraud;
use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    protected $helper;

    public function __construct(
        Antifraud $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->helper->getAntifraudStatus();

        $optionArray = [];
        foreach ($collection as $item) {
            $optionArray[] = ['value' => $item, 'label' => __($item)];
        }

        return $optionArray;
    }
}
