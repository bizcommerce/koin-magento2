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

/**
 * Class CcType
 * @codeCoverageIgnore
 */
class CcType extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * Master, Aura, Visa, Elo, Amex, JCB, HiperCard
     * AX  American Express
    CL  Cabal
    CB  Carte Blanche
    CS  Cencosud
    DC  Diners
    DS  Discover
    EC  Elo
    HC  HiperCard
    PR  Presto
    MG  Magna
    CA  MasterCard
    MD  MasterCard Débito
    NT  Nativa
    TN  Tarjeta Naranja
    TU  Tuya
    VI  Visa
    VD  Visa Débito
     * @var string[]
     */

    protected $_allowedTypes = [
        'AX',
        'CA',
        'CB',
        'CL',
        'CS',
        'DC',
        'DS',
        'EC',
        'HC',
        'TN',
        'TU',
        'VI'
    ];

    protected $methods = [
        'AX' => 'American Express',
        'CA' => 'MasterCard',
        'CB' => 'Carte Blanche',
        'CL' => 'Cabal',
        'CS' => 'Cencosud',
        'DC' => 'Diners',
        'DS' => 'Discover',
        'EC' => 'Elo',
        'HC' => 'HiperCard',
        'TN' => 'Tarjeta Naranja',
        'TU' => 'Tuya',
        'VI' => 'Visa'
    ];

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /**
         * making filter by allowed cards
         */
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->methods as $code => $name) {
            if (in_array($code, $allowed) || !count($allowed)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }

    public function toArray(): array
    {
        $methods = $this->toOptionArray();

        $options = [];
        foreach ($methods as $item) {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }
}
