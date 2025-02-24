<?php
namespace Koin\Payment\Test\Unit\Helper;

use Koin\Payment\Helper\Installments;
use Koin\Payment\Helper\Data;
use Magento\Framework\App\Helper\Context;
use Magento\Directory\Model\PriceCurrency;
use PHPUnit\Framework\TestCase;

class InstallmentsTest extends TestCase
{
    /**
     * @var Installments
     */
    private $installments;

    /**
     * @var Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataHelper;

    /**
     * @var PriceCurrency|\PHPUnit\Framework\MockObject\MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->dataHelper = $this->getMockBuilder(Data::class)
            ->onlyMethods(['getCcConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(PriceCurrency::class)
            ->onlyMethods(['format'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        // Mock the price currency conversion
        $this->priceCurrency
            ->method('format')
            ->willReturnMap([
                [210.2, false, 2, null, null, 'R$ 210,20'],
                [260.15, false, 2, null, null, 'R$ 260,15'],
                [343.43, false, 2, null, null, 'R$ 343,43'],
                [510.05, false, 2, null, null, 'R$ 510,05'],
                [1000.0, false, 2, null, null, 'R$ 1.000,00'],
                [1020.1, false, 2, null, null, 'R$ 1.020,10'],
                [1030.29, false, 2, null, null, 'R$ 1.030,29'],
                [1040.6, false, 2, null, null, 'R$ 1.040,60'],
                [1051.0, false, 2, null, null, 'R$ 1.051,00']
            ]);

        $this->installments = $this->objectManager->getObject(
            Installments::class,
            [
                'context' => $this->createMock(Context::class),
                'priceCurrency' => $this->priceCurrency,
                'helper' => $this->dataHelper
            ]
        );
    }

    public function testGetAllInstallmentsWithMinimumOfOne()
    {
        $this->mockDataHelper([
            'min_installments' => '1',
            'max_installments' => '5',
            'minimum_installment_amount' => '30',
            'interest_rate' => '1',
            'interest_type' => 'compound',
            'has_interest' => '1',
            'max_installments_without_interest' => '1'
        ]);

        $installments = $this->installments->getDefaultInstallments(1000);

        $expectedInstallments = [
            0 => [
                'installments' => 1,
                'interest_rate' => 0.0,
                'installment_price' => 1000.0,
                'total' => 1000.0,
                'formatted_installments_price' => 'R$ 1.000,00',
                'formatted_total' => 'R$ 1.000,00',
                'text' => '1x of R$ 1.000,00 (without interest). Total: R$ 1.000,00',
                'rule' => 0,
                'id' => '1-0'
            ],
            1 => [
                'installments' => 2,
                'interest_rate' => 0.01,
                'installment_price' => 510.05,
                'total' => 1020.1,
                'formatted_installments_price' => 'R$ 510,05',
                'formatted_total' => 'R$ 1.020,10',
                'text' => '2x of R$ 510,05 (with interest). Total: R$ 1.020,10',
                'rule' => 0,
                'id' => '2-0'
            ],
            2 => [
                'installments' => 3,
                'interest_rate' => 0.01,
                'installment_price' => 343.43,
                'total' => 1030.29,
                'formatted_installments_price' => 'R$ 343,43',
                'formatted_total' => 'R$ 1.030,29',
                'text' => '3x of R$ 343,43 (with interest). Total: R$ 1.030,29',
                'rule' => 0,
                'id' => '3-0'
            ],
            3 => [
                'installments' => 4,
                'interest_rate' => 0.01,
                'installment_price' => 260.15,
                'total' => 1040.60,
                'formatted_installments_price' => 'R$ 260,15',
                'formatted_total' => 'R$ 1.040,60',
                'text' => '4x of R$ 260,15 (with interest). Total: R$ 1.040,60',
                'rule' => 0,
                'id' => '4-0'
            ],
            4 => [
                'installments' => 5,
                'interest_rate' => 0.01,
                'installment_price' => 210.2,
                'total' => 1051.0,
                'formatted_installments_price' => 'R$ 210,20',
                'formatted_total' => 'R$ 1.051,00',
                'text' => '5x of R$ 210,20 (with interest). Total: R$ 1.051,00',
                'rule' => 0,
                'id' => '5-0'
            ],
        ];

        $this->assertEquals($expectedInstallments, $installments);
    }

    public function testGetAllInstallmentsWithMinimumOfTwo()
    {
        $this->mockDataHelper([
            'enable_default_installment' => '1',
            'min_installments' => '2',
            'max_installments' => '5',
            'minimum_installment_amount' => '30',
            'interest_rate' => '1',
            'interest_type' => 'compound',
            'has_interest' => '1',
            'max_installments_without_interest' => '1'
        ]);

        $installments = $this->installments->getAllInstallments(1000);

        $expectedInstallments = [
            0 => [
                'installments' => 2,
                'interest_rate' => 0.01,
                'installment_price' => 510.05,
                'total' => 1020.1,
                'formatted_installments_price' => 'R$ 510,05',
                'formatted_total' => 'R$ 1.020,10',
                'text' => '2x of R$ 510,05 (with interest). Total: R$ 1.020,10',
                'rule' => 0,
                'id' => '2-0'
            ],
            1 => [
                'installments' => 3,
                'interest_rate' => 0.01,
                'installment_price' => 343.43,
                'total' => 1030.29,
                'formatted_installments_price' => 'R$ 343,43',
                'formatted_total' => 'R$ 1.030,29',
                'text' => '3x of R$ 343,43 (with interest). Total: R$ 1.030,29',
                'rule' => 0,
                'id' => '3-0'
            ],
            2 => [
                'installments' => 4,
                'interest_rate' => 0.01,
                'installment_price' => 260.15,
                'total' => 1040.60,
                'formatted_installments_price' => 'R$ 260,15',
                'formatted_total' => 'R$ 1.040,60',
                'text' => '4x of R$ 260,15 (with interest). Total: R$ 1.040,60',
                'rule' => 0,
                'id' => '4-0'
            ],
            3 => [
                'installments' => 5,
                'interest_rate' => 0.01,
                'installment_price' => 210.2,
                'total' => 1051.0,
                'formatted_installments_price' => 'R$ 210,20',
                'formatted_total' => 'R$ 1.051,00',
                'text' => '5x of R$ 210,20 (with interest). Total: R$ 1.051,00',
                'rule' => 0,
                'id' => '5-0'
            ],
        ];

        $this->assertEquals($expectedInstallments, $installments);
    }

    public function testGetAllInstallmentsWithMinimumOfThree()
    {
        $this->mockDataHelper([
            'enable_default_installment' => '1',
            'min_installments' => '3',
            'max_installments' => '5',
            'minimum_installment_amount' => '30',
            'interest_rate' => '1',
            'interest_type' => 'compound',
            'has_interest' => '1',
            'max_installments_without_interest' => '1'
        ]);

        $installments = $this->installments->getAllInstallments(1000);

        $expectedInstallments = [
            0 => [
                'installments' => 3,
                'interest_rate' => 0.01,
                'installment_price' => 343.43,
                'total' => 1030.29,
                'formatted_installments_price' => 'R$ 343,43',
                'formatted_total' => 'R$ 1.030,29',
                'text' => '3x of R$ 343,43 (with interest). Total: R$ 1.030,29',
                'rule' => 0,
                'id' => '3-0'
            ],
            1 => [
                'installments' => 4,
                'interest_rate' => 0.01,
                'installment_price' => 260.15,
                'total' => 1040.60,
                'formatted_installments_price' => 'R$ 260,15',
                'formatted_total' => 'R$ 1.040,60',
                'text' => '4x of R$ 260,15 (with interest). Total: R$ 1.040,60',
                'rule' => 0,
                'id' => '4-0'
            ],
            2 => [
                'installments' => 5,
                'interest_rate' => 0.01,
                'installment_price' => 210.2,
                'total' => 1051.0,
                'formatted_installments_price' => 'R$ 210,20',
                'formatted_total' => 'R$ 1.051,00',
                'text' => '5x of R$ 210,20 (with interest). Total: R$ 1.051,00',
                'rule' => 0,
                'id' => '5-0'
            ],
        ];

        $this->assertEquals($expectedInstallments, $installments);
    }

    protected function mockDataHelper($map): void
    {
        $this->dataHelper->method('getCcConfig')
            ->will($this->returnCallback(function ($arg) use ($map){
                return $map[$arg] ?? '';
            }));
    }
}
