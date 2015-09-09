<?php

namespace Unit\Suites\DataPool\SearchEngine\SearchCriteria;

use Brera\DataPool\SearchEngine\SearchCriteria\AbstractSearchCriterionTest;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessThan
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionLessThanTest extends AbstractSearchCriterionTest
{
    /**
     * @return string
     */
    final protected function getOperationName()
    {
        return 'LessThan';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues()
    {
        return [
            ['2', '1'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues()
    {
        return[
            ['1', '2'],
        ];
    }
}