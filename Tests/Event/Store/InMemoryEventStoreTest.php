<?php
namespace Caveja\CQRS\Tests\Event\Store;

use Caveja\CQRS\Event\Store\InMemoryEventStore;
use Foodlogger\Domain\Event\Bus\EventPublisherInterface;

class InMemoryEventStoreTest extends EventStoreTest
{
    /**
     * @param  EventPublisherInterface $publisher
     * @return InMemoryEventStore
     */
    protected function createEventStore(EventPublisherInterface $publisher)
    {
        return new InMemoryEventStore($publisher);
    }
}
