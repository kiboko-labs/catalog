<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchFactory
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\RestApi\RestApiFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductSearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSearchFactory
     */
    private $factory;

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new UnitTestFactory($this));
        $masterFactory->register(new UnitTestFactory($this));

        $this->factory = new ProductSearchFactory();

        $masterFactory->register($this->factory);
    }

    public function testFactoryInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Factory::class, $this->factory);
    }

    public function testRegistersDelegateFactoryInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FactoryWithCallback::class, $this->factory);
    }

    public function testProductSearchApiEndpointIsRegistered()
    {
        $endpointKey = 'get_product';
        $apiVersion = 1;

        $mockApiRequestHandlerLocator = $this->createMock(ApiRequestHandlerLocator::class);
        $mockApiRequestHandlerLocator->expects($this->once())->method('register')
            ->with($endpointKey, $apiVersion, $this->isInstanceOf(ProductSearchApiV1GetRequestHandler::class));

        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)->setMethods(
            ['register', 'getApiRequestHandlerLocator']
        )->getMock();
        $stubMasterFactory->method('getApiRequestHandlerLocator')->willReturn($mockApiRequestHandlerLocator);

        $this->factory->factoryRegistrationCallback($stubMasterFactory);
    }
}
