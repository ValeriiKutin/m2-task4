<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace PayPal\Braintree\Test\Unit\Model\Adminhtml\System\Config;

use PayPal\Braintree\Model\Adminhtml\System\Config\Country;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CountryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PayPal\Braintree\Model\Adminhtml\System\Config\Country
     */
    protected $model;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $countryCollectionMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->countryCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            Country::class,
            [
                'countryCollection' => $this->countryCollectionMock,
            ]
        );
    }

    /**
     * @covers \PayPal\Braintree\Model\Adminhtml\System\Config\Country::toOptionArray
     */
    public function testToOptionArrayMultiSelect()
    {
        $countries = [
            [
                'value' => 'US',
                'label' => 'United States',
            ],
            [
                'value' => 'UK',
                'label' => 'United Kingdom',
            ],
        ];
        $this->initCountryCollectionMock($countries);

        $this->assertEquals($countries, $this->model->toOptionArray(true));
    }

    /**
     * @covers \PayPal\Braintree\Model\Adminhtml\System\Config\Country::toOptionArray
     */
    public function testToOptionArray()
    {
        $countries = [
            [
                'value' => 'US',
                'label' => 'United States',
            ],
            [
                'value' => 'UK',
                'label' => 'United Kingdom',
            ],
        ];
        $this->initCountryCollectionMock($countries);

        $header = ['value' => '', 'label' => new Phrase('--Please Select--')];
        array_unshift($countries, $header);

        $this->assertEquals($countries, $this->model->toOptionArray());
    }

    /**
     * @covers \PayPal\Braintree\Model\Adminhtml\System\Config\Country::isCountryRestricted
     * @param string $countryId
     * @dataProvider countryDataProvider
     */
    public function testIsCountryRestricted($countryId)
    {
        static::assertTrue($this->model->isCountryRestricted($countryId));
    }

    /**
     * Get simple list of not available braintree countries
     * @return array
     */
    public function countryDataProvider()
    {
        return [
            ['MM'],
            ['IR'],
            ['SD'],
            ['BY'],
            ['CI'],
            ['CD'],
            ['CG'],
            ['IQ'],
            ['LR'],
            ['LB'],
            ['KP'],
            ['SL'],
            ['SY'],
            ['ZW'],
            ['AL'],
            ['BA'],
            ['MK'],
            ['ME'],
            ['RS']
        ];
    }

    /**
     * Init country collection mock
     * @param array $countries
     */
    protected function initCountryCollectionMock(array $countries)
    {
        $this->countryCollectionMock->expects(static::once())
            ->method('addFieldToFilter')
            ->willReturnSelf();
        $this->countryCollectionMock->expects(static::once())
            ->method('loadData')
            ->willReturnSelf();
        $this->countryCollectionMock->expects(static::once())
            ->method('toOptionArray')
            ->willReturn($countries);
    }
}
