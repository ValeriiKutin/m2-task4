<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace PayPal\Braintree\Test\Unit\Model\Report;

use Braintree\Transaction;
use Braintree\Transaction\PayPalDetails;
use PayPal\Braintree\Model\Report\Row\TransactionMap;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Phrase\RendererInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test for class \PayPal\Braintree\Model\Report\\Row\TransactionMap
 */
class TransactionMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Transaction|MockObject
     */
    private Transaction|MockObject $transactionStub;

    /**
     * @var AttributeValueFactory|MockObject
     */
    private AttributeValueFactory|MockObject $attributeValueFactoryMock;

    /**
     * @var RendererInterface|MockObject
     */
    private RendererInterface|MockObject $defaultRenderer;

    /**
     * @var RendererInterface|MockObject
     */
    private RendererInterface|MockObject $rendererMock;

    /**
     * Setup
     */
    protected function setUp(): void
    {
        $this->attributeValueFactoryMock = $this->getMockBuilder(AttributeValueFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->defaultRenderer = Phrase::getRenderer();
        $this->rendererMock = $this->getMockBuilder(RendererInterface::class)
            ->getMock();
    }

    /**
     * Get items
     *
     * @param array $transaction
     * @dataProvider getConfigDataProvider
     */
    public function testGetCustomAttributes(array $transaction)
    {
        $this->transactionStub = Transaction::factory($transaction);

        $fields = TransactionMap::$simpleFieldsMap;
        $fieldsQty = count($fields);

        $this->attributeValueFactoryMock->expects($this->exactly($fieldsQty))
            ->method('create')
            ->willReturnCallback(function () {
                return new AttributeValue();
            });

        $map = new TransactionMap(
            $this->attributeValueFactoryMock,
            $this->transactionStub
        );

        Phrase::setRenderer($this->rendererMock);

        /** @var AttributeValue[] $result */
        $result = $map->getCustomAttributes();

        $this->assertEquals($fieldsQty, count($result));
        $this->assertInstanceOf(AttributeValue::class, $result[1]);
        $this->assertEquals($transaction['id'], $result[0]->getValue());
        $this->assertEquals($transaction['paypalDetails']->paymentId, $result[4]->getValue());
        $this->assertEquals(
            $transaction['createdAt']->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            $result[6]->getValue()
        );
        $this->assertEquals(implode(', ', $transaction['refundIds']), $result[11]->getValue());
        $this->assertEquals($transaction['merchantAccountId'], $result[1]->getValue());
        $this->assertEquals($transaction['orderId'], $result[2]->getValue());
        $this->assertEquals($transaction['amount'], $result[7]->getValue());
        $this->assertEquals($transaction['processorSettlementResponseCode'], $result[8]->getValue());
        $this->assertEquals($transaction['processorSettlementResponseText'], $result[10]->getValue());
        $this->assertEquals($transaction['settlementBatchId'], $result[12]->getValue());
        $this->assertEquals($transaction['currencyIsoCode'], $result[13]->getValue());

        $this->rendererMock->method('render')
            ->with([$transaction['paymentInstrumentType']])
            ->willReturn('Credit card');
        $this->assertEquals('Credit card', $result[3]->getValue()->render());

        $this->rendererMock->method('render')
            ->with([$transaction['type']])
            ->willReturn('sale');
        $this->assertEquals('sale', $result[5]->getValue()->render());

        $this->rendererMock->method('render')
            ->with([$transaction['status']])
            ->willReturn('pending_for_settlement');
        $this->assertEquals('pending_for_settlement', $result[9]->getValue()->render());
    }

    /**
     * Config data provider
     *
     * @return array
     * @throws \Exception
     */
    public static function getConfigDataProvider()
    {
        return [
            [
                'transaction' => [
                    'id' => 1,
                    'createdAt' => new \DateTime(),
                    'paypalDetails' => new PayPalDetails(['paymentId' => 10]),
                    'refundIds' => [1, 2, 3, 4, 5],
                    'merchantAccountId' => 'MerchantId',
                    'orderId' => 1,
                    'paymentInstrumentType' => 'credit_card',
                    'type' => 'sale',
                    'amount' => '$19.99',
                    'processorSettlementResponseCode' => 1,
                    'status' => 'pending_for_settlement',
                    'processorSettlementResponseText' => 'sample text',
                    'settlementBatchId' => 2,
                    'currencyIsoCode' => 'USD'
                ]
            ]
        ];
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        Phrase::setRenderer($this->defaultRenderer);
    }
}
