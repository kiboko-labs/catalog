<?php


namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;

class SampleUrlPathKeyGenerator implements UrlPathKeyGenerator
{
    /**
     * @param HttpUrl $url
     * @param Context $context
     * @return string
     */
    public function getUrlKeyForUrlInContext(HttpUrl $url, Context $context)
    {
        return $this->getUrlKeyForPathInContext($url->getPathRelativeToWebFront(), $context);
    }

    /**
     * @param string $path
     * @param Context $context
     * @return string
     */
    public function getUrlKeyForPathInContext($path, Context $context)
    {
        $key = $this->prependSlashIfMissing((string) $path) . '_' . $context->getId();
        return preg_replace('#[^a-z0-9:_-]#i', '_', $key);
    }

    /**
     * @param string $path
     * @return string
     */
    private function prependSlashIfMissing($path)
    {
        return preg_replace('#^([^/])#', '/$1', $path);
    }
}