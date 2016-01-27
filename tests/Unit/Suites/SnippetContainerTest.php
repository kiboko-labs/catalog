<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\InvalidSnippetContainerCodeException;

/**
 * @covers \LizardsAndPumpkins\SnippetContainer
 */
class SnippetContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $code
     * @param string[] $containedSnippetCodes
     * @return SnippetContainer
     */
    private function createInstance($code, $containedSnippetCodes)
    {
        return new SnippetContainer($code, $containedSnippetCodes);
    }

    public function testItReturnsTheContainerCode()
    {
        $container = $this->createInstance('test', ['foo', 'bar']);

        $this->assertSame('test', $container->getCode());
    }

    public function testItReturnsTheContainedSnippetCodes()
    {
        $container = $this->createInstance('test', ['abc', 'def']);
        $this->assertSame(['abc', 'def'], $container->getSnippetCodes());
    }

    public function testItThrowsAnExceptionIfTheContainerCodeIsNotAString()
    {
        $this->setExpectedException(
            InvalidSnippetContainerCodeException::class,
            'The snippet container code has to be a string'
        );

        new SnippetContainer(12, []);
    }

    public function testItThrowsAnExceptionIfTheContainerCodeIsTooShort()
    {
        $this->setExpectedException(
            InvalidSnippetContainerCodeException::class,
            'The snippet container code has to be at least 2 characters long'
        );

        $this->createInstance('i', []);
    }

    public function testItReturnsAnAssociativeArray()
    {
        $container = $this->createInstance('test', ['foo', 'bar']);
        $jsonData = $container->toArray();
        
        $this->assertSame(['test' => ['foo', 'bar']], $jsonData);
    }
}