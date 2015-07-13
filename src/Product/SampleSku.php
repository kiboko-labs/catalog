<?php

namespace Brera\Product;

class SampleSku implements Sku
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @param string $skuString
     */
    private function __construct($skuString)
    {
        $this->sku = $skuString;
    }

    /**
     * @param string $skuString
     * @throws InvalidSkuException
     * @return SampleSku
     */
    public static function fromString($skuString)
    {
        if (is_string($skuString) || (is_object($skuString) && method_exists($skuString, '__toString'))) {
            $skuString = trim($skuString);
        }

        if ((!is_string($skuString) && !is_int($skuString) && !is_float($skuString)) || empty($skuString)) {
            throw new InvalidSkuException(
                'Expecting integer, decimal, non-empty string or an object convertible to non-empty string.'
            );
        }

        return new self($skuString);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->sku;
    }
}