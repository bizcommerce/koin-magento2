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

class Street implements \Magento\Framework\Data\OptionSourceInterface
{

   public function toOptionArray()
    {
        return [
            '0' => __('Street Line %1', 1),
            '1' => __('Street Line %1', 2),
            '2' => __('Street Line %1', 3),
            '3' => __('Street Line %1', 4),
        ];
    }
}
