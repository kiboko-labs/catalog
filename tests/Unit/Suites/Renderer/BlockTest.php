<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Renderer\Block
 */
class BlockTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.phtml';

        if (file_exists($filePath) && is_file($filePath)) {
            chmod($filePath, '600');
            unlink($filePath);
        }
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileDoesNotExist()
    {
        $stubDataObject = $this->getMock(ProjectionSourceData::class);
        $block = new Block('foo.phtml', $stubDataObject);
        $block->render();
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\TemplateFileNotReadableException
     */
    public function itShouldThrowAnExceptionIfTemplateFileIsNotReadable()
    {
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'some-file-name.phtml';

        touch($filePath);
        chmod($filePath, 000);

        $stubDataObject = $this->getMock(ProjectionSourceData::class);

        $block = new Block($filePath, $stubDataObject);
        $block->render();
    }
}