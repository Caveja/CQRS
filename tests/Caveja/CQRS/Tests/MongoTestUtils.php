<?php
namespace Caveja\CQRS\Tests;
use Doctrine\MongoDB\Collection;

/**
 * Class MongoTestUtils
 * @package Caveja\CQRS\Tests
 */
trait MongoTestUtils
{
    /**
     * @param $fieldName
     * @param  Collection $collection
     * @return bool
     */
    private function isFieldIndexedUnique(Collection $collection, $fieldName)
    {
        $indexes = $collection->getIndexInfo();
        foreach ($indexes as $index) {
            if (isset($index['key']) && isset($index['key'][$fieldName])) {
                return isset($index['unique']) && $index['unique'];
            }
        }

        return false;
    }
}
