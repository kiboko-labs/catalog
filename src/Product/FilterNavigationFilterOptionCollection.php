<?php

namespace LizardsAndPumpkins\Product;

class FilterNavigationFilterOptionCollection implements \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * @var FilterNavigationFilterOption[]
     */
    private $filterOptions = [];

    /**
     * @return int
     */
    public function count()
    {
        return count($this->filterOptions);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->filterOptions);
    }

    public function add(FilterNavigationFilterOption $filterValue)
    {
        $this->filterOptions[] = $filterValue;
    }

    /**
     * @return FilterNavigationFilterOption[]
     */
    public function getOptions()
    {
        return $this->filterOptions;
    }

    /**
     * @return mixed[]
     */
    function jsonSerialize()
    {
        return $this->filterOptions;
    }
}
