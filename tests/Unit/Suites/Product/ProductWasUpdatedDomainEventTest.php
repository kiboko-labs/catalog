<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent
 */
class ProductWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var ProductWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        /** @var ProductId|\PHPUnit_Framework_MockObject_MockObject $stubProductId */
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProduct = $this->getMock(SimpleProduct::class, [], [], '', false);
        $this->domainEvent = new ProductWasUpdatedDomainEvent($stubProductId, $this->stubProduct);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductIsReturned()
    {
        $result = $this->domainEvent->getProduct();
        $this->assertSame($this->stubProduct, $result);
    }
}
