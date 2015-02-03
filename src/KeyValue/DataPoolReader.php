<?php

namespace Brera\KeyValue;

use Brera\Http\HttpUrl;
use Brera\Product\PoCSku;
use Brera\Product\ProductId;

class DataPoolReader
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var KeyValueStoreKeyGenerator
     */
    private $keyValueStoreKeyGenerator;

    /**
     * @param KeyValueStore $keyValueStore
     * @param KeyValueStoreKeyGenerator $keyValueStoreKeyGenerator
     */
    function __construct(KeyValueStore $keyValueStore, KeyValueStoreKeyGenerator $keyValueStoreKeyGenerator)
    {
        $this->keyValueStore = $keyValueStore;
        $this->keyValueStoreKeyGenerator = $keyValueStoreKeyGenerator;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getSnippet($key)
    {
        $this->validateKey($key);

        return $this->keyValueStore->get($key);
    }

    /**
     * @param ProductId $productId
     * @return mixed
     */
    public function getPoCProductHtml(ProductId $productId)
    {
        $key = $this->keyValueStoreKeyGenerator->createPoCProductHtmlKey($productId);

        return $this->keyValueStore->get($key);
    }

    /**
     * @param HttpUrl $url
     * @return ProductId
     */
    public function getProductIdBySeoUrl(HttpUrl $url)
    {
        $key = $this->keyValueStoreKeyGenerator->createPoCProductSeoUrlToIdKey($url);
        $skuString = $this->keyValueStore->get($key);
        $sku = PoCSku::fromString($skuString);

        return ProductId::fromSku($sku);
    }

    /**
     * @param HttpUrl $url
     * @return bool
     */
    public function hasProductSeoUrl(HttpUrl $url)
    {
        $key = $this->keyValueStoreKeyGenerator->createPoCProductSeoUrlToIdKey($url);

        return $this->keyValueStore->has($key);
    }

    /**
     * @param string $key
     * @return string[]
     */
    public function getChildSnippetKeys($key)
    {
        $this->validateKey($key);
        $json = $this->keyValueStore->get($key);
        $this->validateJson($key, $json);
        $list = $this->decodeJsonArray($key, $json);

        return $list;
    }

    /**
     * @param string[] $keys
     * @return string[]
     */
    public function getSnippets($keys)
    {
        if (!is_array($keys)) {
            throw new \RuntimeException(
                sprintf('multiGet needs an array to operated on, your keys is of type %s.', gettype($keys))
            );
        }
        foreach ($keys as $key) {
            $this->validateKey($key);
        }

        return $this->keyValueStore->multiGet($keys);
    }

    /**
     * @param string $key
     */
    private function validateKey($key)
    {
        if (!is_string($key)) {
            throw new \RuntimeException('Key is not of type string.');
        }
    }

    /**
     * @param string $key
     * @param string $json
     */
    private function validateJson($key, $json)
    {
        if (!is_string($json)) {
            throw new \RuntimeException(
                sprintf(
                    'Expected the value for key "%s" to be a string containing JSON but found "%s".',
                    $key, gettype($json))
            );
        }
    }

    /**
     * @param string $key
     * @param string $json
     * @return string[]
     */
    private function decodeJsonArray($key, $json)
    {
        $result = json_decode($json, true);

        if ($result === false) {
            $result = [];
        }
        if (!is_array($result) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(sprintf('List for key "%s" is no valid JSON.', $key));
        }

        return $result;
    }

}
