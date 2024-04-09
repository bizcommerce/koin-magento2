<?php
declare(strict_types=1);

namespace Koin\Payment\Ui\Component\Listing\Column\InstallmentsRules;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {

                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'koin/installments_rules/edit',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                    '__disableTmpl' => true
                ];

                $item[$this->getData('name')]['enable'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'koin/installments_rules/enable',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('Enable'),
                    'hidden' => false,
                    '__disableTmpl' => true
                ];

                $item[$this->getData('name')]['disable'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'koin/installments_rules/disable',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('Disable'),
                    'hidden' => false,
                    '__disableTmpl' => true
                ];

                $item[$this->getData('name')]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'koin/installments_rules/delete',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('Delete'),
                    'hidden' => false,
                    '__disableTmpl' => true
                ];
            }
        }

        return $dataSource;
    }
}
