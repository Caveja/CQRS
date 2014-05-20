<?php
namespace Caveja\CQRS\Tests\Event\Store;

use Doctrine\MongoDB\Database;
use Caveja\CQRS\Event\Bus\EventPublisherInterface;

/**
 * Class MongoEventStoreTest
 * @package Caveja\CQRS\Tests\Event\Store
 * @group database
 */
class GregEventStoreTest extends EventStoreTest
{
    protected function createEventStore(EventPublisherInterface $eventPublisher)
    {
        $this->markTestSkipped('Missing EventStore client');
    }
}
