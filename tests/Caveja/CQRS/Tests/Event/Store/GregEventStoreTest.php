<?php
namespace Caveja\CQRS\Tests\Event\Store;

use Caveja\CQRS\Event\Bus\EventPublisherInterface;
use Caveja\CQRS\Event\Store\GregEventStore;
use EventStore\Connection;

/**
 * Class MongoEventStoreTest
 * @package Caveja\CQRS\Tests\Event\Store
 * @group end2end
 */
class GregEventStoreTest extends EventStoreTest
{
    protected function createEventStore(EventPublisherInterface $eventPublisher)
    {
        return new GregEventStore(Connection::create(), $eventPublisher);
    }
}
