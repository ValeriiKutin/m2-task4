<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);
namespace PayPal\Braintree\Test\Unit\Block;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Braintree\Block\Form;
use PayPal\Braintree\Gateway\Config\Config as GatewayConfig;
use PayPal\Braintree\Model\Adminhtml\Source\CcType;
use PayPal\Braintree\Model\Ui\ConfigProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    public static array $baseCardTypes = [
        'AE' => 'American Express',
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'JBC' => 'JBC',
        'MI' => 'Maestro',
    ];

    /**
     * @var array
     */
    public static array $configCardTypes = [
        'AE', 'VI', 'MC', 'DI', 'JBC'
    ];

    /**
     * @var Form|MockObject
     */
    private Form|MockObject $block;

    /**
     * @var Quote|MockObject
     */
    private Quote|MockObject $sessionQuote;

    /**
     * @var Config|MockObject
     */
    private MockObject|Config $gatewayConfig;

    /**
     * @var CcType|MockObject
     */
    private MockObject|CcType $ccType;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface|MockObject $storeManager;

    /**
     * @var Data|MockObject
     */
    private Data|MockObject $paymentDataHelper;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->initCcTypeMock();
        $this->initSessionQuoteMock();
        $this->initGatewayConfigMock();

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->paymentDataHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethodInstance'])
            ->getMock();

        $managerHelper = new ObjectManager($this);
        $this->block = $managerHelper->getObject(Form::class, [
            'paymentConfig' => $managerHelper->getObject(Config::class),
            'sessionQuote' => $this->sessionQuote,
            'gatewayConfig' => $this->gatewayConfig,
            'ccType' => $this->ccType,
            'storeManager' => $this->storeManager
        ]);

        $managerHelper->setBackwardCompatibleProperty($this->block, 'paymentDataHelper', $this->paymentDataHelper);
    }

    /**
     * @param string $countryId
     * @param array $availableTypes
     * @param array $expected
     * @dataProvider countryCardTypesDataProvider
     */
    public function testGetCcAvailableTypes(
        string $countryId,
        array $availableTypes,
        array $expected
    ) {
        $this->sessionQuote->expects(static::once())
            ->method('getCountryId')
            ->willReturn($countryId);

        $this->gatewayConfig->expects(static::once())
            ->method('getAvailableCardTypes')
            ->willReturn(self::$configCardTypes);

        $this->gatewayConfig->expects(static::once())
            ->method('getCountryAvailableCardTypes')
            ->with($countryId)
            ->willReturn($availableTypes);

        $result = $this->block->getCcAvailableTypes();
        static::assertEquals($expected, array_values($result));
    }

    /**
     * Get country card types testing data
     *
     * @return array
     */
    public static function countryCardTypesDataProvider(): array
    {
        return [
            ['US', ['AE', 'VI'], ['American Express', 'Visa']],
            ['UK', ['VI'], ['Visa']],
            ['CA', ['MC'], ['MasterCard']],
            ['UA', [], ['American Express', 'Visa', 'MasterCard', 'Discover', 'JBC']]
        ];
    }

    /**
     * Test is vault enabled
     *
     * @throws Exception
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testIsVaultEnabled()
    {
        $storeId = 1;
        $store = $this->getMockForAbstractClass(StoreInterface::class);

        $this->sessionQuote->method('getStoreId')
            ->willReturn(0);

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->willReturn($store);

        $store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $vaultPayment = $this->getMockForAbstractClass(VaultPaymentInterface::class);
        $this->paymentDataHelper->expects(static::once())
            ->method('getMethodInstance')
            ->with(ConfigProvider::CC_VAULT_CODE)
            ->willReturn($vaultPayment);

        $vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn(true);

        static::assertTrue($this->block->isVaultEnabled());
    }

    /**
     * Create mock for credit card type
     */
    private function initCcTypeMock(): void
    {
        $this->ccType = $this->getMockBuilder(CcType::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllowedTypes'])
            ->getMock();

        $this->ccType->expects(static::any())
            ->method('getAllowedTypes')
            ->willReturn(self::$baseCardTypes);
    }

    /**
     * Create mock for session quote
     */
    private function initSessionQuoteMock(): void
    {
        $this->sessionQuote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuote'])
            ->addMethods(['getStoreId', 'getBillingAddress', 'getCountryId', '__wakeup'])
            ->getMock();

        $this->sessionQuote->expects(static::any())
            ->method('getQuote')
            ->willReturnSelf();
        $this->sessionQuote->expects(static::any())
            ->method('getBillingAddress')
            ->willReturnSelf();
    }

    /**
     * Create mock for gateway config
     */
    private function initGatewayConfigMock(): void
    {
        $this->gatewayConfig = $this->getMockBuilder(GatewayConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountryAvailableCardTypes', 'getAvailableCardTypes'])
            ->getMock();
    }
}
