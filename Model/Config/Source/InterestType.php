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

namespace Koin\Payment\Model\Config\Source;

class InterestType implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            'per_installments' => __('Per Installments'),
            'price' => __('Amortization'),
            'compound' => __('Compound'),
            'simple' => __('Simple')
        ];
    }

    public function toArray(): array
    {
        $options = [];
        foreach ($this->toOptionArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $options;
    }
}
