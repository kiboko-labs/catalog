<?php

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

interface SnippetKeyGeneratorLocator
{
    /**
     * @param string $snippetCode
     * @return bool
     */
    public function canHandle($snippetCode);

    /**
     * @param string $snippetCode
     * @return SnippetKeyGenerator
     */
    public function getKeyGeneratorForSnippetCode($snippetCode);
}
