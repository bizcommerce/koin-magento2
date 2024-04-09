<?php
/**
 * Biz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Biz.com license that is
 * available through the world-wide-web at this URL:
 * https://www.bizcommerce.com.br/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Biz
 * @package     Koin_Payment
 * @copyright   Copyright (c) Biz (https://www.bizcommerce.com.br/)
 * @license     https://www.bizcommerce.com.br/LICENSE.txt
 */

namespace Koin\Payment\Model\Adminhtml\Source;

class Installments implements \Magento\Framework\Data\OptionSourceInterface
{

   public function toOptionArray()
    {
        return [
            1 => __('in cash payments'),
            2 => __('%1 installments', 2),
            3 => __('%1 installments', 3),
            4 => __('%1 installments', 4),
            5 => __('%1 installments', 5),
            6 => __('%1 installments', 6),
            7 => __('%1 installments', 7),
            8 => __('%1 installments', 8),
            9 => __('%1 installments', 9),
            10 => __('%1 installments', 10),
            11 => __('%1 installments', 11),
            12 => __('%1 installments', 12)
        ];
    }
}
