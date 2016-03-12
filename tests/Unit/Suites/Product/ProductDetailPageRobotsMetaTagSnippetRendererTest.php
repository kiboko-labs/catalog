<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductDetailPageRobotsMetaTagSnippetRenderer
 */
class ProductDetailPageRobotsMetaTagSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailPageRobotsMetaTagSnippetRenderer
     */
    private $renderer;

    /**
     * @var RobotsMetaTagSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRobotsMetaTagRenderer;

    protected function setUp()
    {
        $this->mockRobotsMetaTagRenderer = $this->getMock(RobotsMetaTagSnippetRenderer::class, [], [], '', false);
        $this->renderer = new ProductDetailPageRobotsMetaTagSnippetRenderer($this->mockRobotsMetaTagRenderer);
    }

    public function testIsASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testDelegatesToRobotsMetaTagSnippetRenderer()
    {
        $stubContext = $this->getMock(Context::class);
        /** @var ProductView|\PHPUnit_Framework_MockObject_MockObject $stubProductView */
        $stubProductView = $this->getMock(ProductView::class);
        $stubProductView->method('getContext')->willReturn($stubContext);

        $dummyReturnValue = ['dummy'];
        $this->mockRobotsMetaTagRenderer->expects($this->once())
            ->method('render')
            ->with($stubContext)
            ->willReturn($dummyReturnValue);
        
        $this->assertSame($dummyReturnValue, $this->renderer->render($stubProductView));
    }
}