<?php
namespace Caveja\CQRS\Tests\Event\Store;

use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Event\Store\EventStoreInterface;
use Caveja\CQRS\Event\Bus\EventPublisherInterface;
use Caveja\CQRS\Event\Bus\EventSubscriberInterface;
use Caveja\CQRS\Event\Bus\InMemoryEventBus;
use Caveja\CQRS\Event\DomainEvent;
use ValueObjects\Identity\UUID;

abstract class EventStoreTest extends \PHPUnit_Framework_TestCase implements EventSubscriberInterface
{
    /**
     * @var EventInterface[]
     */
    private $events = [];

    protected function tearDown()
    {
        $this->events = [];
    }

    public function testAppendIncreasesCount()
    {
        $store = $this->createEventStore(new InMemoryEventBus());

        $aggregateId = new UUID();
        $this->assertSame(0, $store->count($aggregateId));
        $event = new DomainEvent(new UUID(), 'SomethingHappened', []);

        $store->saveEvents($aggregateId, [$event]);
        $this->assertSame(1, $store->count($aggregateId));
    }

    /**
     * @param  EventPublisherInterface $eventPublisher
     * @return EventStoreInterface
     */
    abstract protected function createEventStore(EventPublisherInterface $eventPublisher);

    public function testGetLast()
    {
        $store = $this->createEventStore(new InMemoryEventBus());

        $aggregateId = new UUID();

        $event1 = new DomainEvent(new UUID(), 'SomethingHappened', []);
        $event2 = new DomainEvent(new UUID(), 'SomethingElseHappened', []);
        $event2->setVersion(EventStoreInterface::VERSION_FIRST + 1);

        $store->saveEvents($aggregateId, [$event1, $event2]);

        $this->assertEquals($event2, $store->last($aggregateId));
    }

    public function testGetLastWithDifferentAggregateId()
    {
        $store = $this->createEventStore(new InMemoryEventBus());

        $aggregateId = new UUID();

        $event1 = new DomainEvent(new UUID(), 'SomethingHappened', []);
        $event2 = new DomainEvent(new UUID(), 'SomethingElseHappened', []);

        $store->saveEvents(new UUID(), [$event2]);
        $store->saveEvents($aggregateId, [$event1]);

        $this->assertEquals($event1, $store->last($aggregateId));
    }

    public function testIteration()
    {
        $store = $this->createEventStore(new InMemoryEventBus());

        $event1 = new DomainEvent(new UUID(), 'SomethingHappened', []);
        $event1->setVersion(EventStoreInterface::VERSION_FIRST);
        $event2 = new DomainEvent(new UUID(), 'SomethingElseHappened', []);
        $event2->setVersion(EventStoreInterface::VERSION_FIRST + 1);

        $aggregateId = new UUID();

        $store->saveEvents($aggregateId, [$event1]);
        $store->saveEvents($aggregateId, [$event2]);

        $events = iterator_to_array($store->getEventsForAggregate($aggregateId));

        $this->assertEquals(2, count($events));

        $this->assertEquals($event1, array_shift($events));
        $this->assertEquals($event2, array_shift($events));
    }

    /**
     * @expectedException Caveja\CQRS\Exception\AggregateNotFoundException
     */
    public function testLastThrowsExceptionOnUnexistentAggregateId()
    {
        $this->createEventStore(new InMemoryEventBus())->last(new UUID());
    }

    /**
     * @expectedException Caveja\CQRS\Exception\AggregateNotFoundException
     */
    public function testGetIteratorThrowsExceptionOnUnexistentAggregateId()
    {
        $this->createEventStore(new InMemoryEventBus())->getEventsForAggregate(new UUID());
    }

    /**
     * @dataProvider wrongVersions
     * @expectedException Caveja\CQRS\Exception\ConcurrencyException
     */
    public function testSaveWithWrongExpectedVersionThrowsConcurrencyException($wrongVersion)
    {
        $store = $this->createEventStore(new InMemoryEventBus());

        $event1 = new DomainEvent(new UUID(), 'SomethingHappened', []);
        $event1->setVersion(EventStoreInterface::VERSION_FIRST);
        $event2 = new DomainEvent(new UUID(), 'SomethingElseHappened', []);
        $event1->setVersion(EventStoreInterface::VERSION_FIRST + 1);

        $aggregateId = new UUID();

        $store->saveEvents($aggregateId, [$event1], EventStoreInterface::VERSION_ANY);
        $store->saveEvents($aggregateId, [$event2], $wrongVersion);
    }

    public static function wrongVersions()
    {
        return [
            [EventStoreInterface::VERSION_NEW],
            [1],
            [2],
        ];
    }

    public function testFirstVersionIsZero()
    {
        $store = $this->createEventStore(new InMemoryEventBus());

        $event1 = new DomainEvent(new UUID(), 'SomethingHappened', []);

        $aggregateId = new UUID();

        $store->saveEvents($aggregateId, [$event1], EventStoreInterface::VERSION_NEW);
        $events = iterator_to_array($store->getEventsForAggregate($aggregateId));

        $this->assertCount(1, $events);
        $this->assertSame(EventStoreInterface::VERSION_FIRST, array_pop($events)->getVersion());
    }

    public function testStoredEventsArePublished()
    {
        $events[] = DomainEvent::create('SomethingHappened', []);
        $events[] = DomainEvent::create('SomethingElseHappened', []);

        $store = $this->createEventStore($publisher = new InMemoryEventBus());
        $publisher->subscribe($this);

        $store->saveEvents(new UUID(), $events);

        $this->assertEquals($events, $this->events);
    }

    public function testMultipleEventsNotThrowingExceptions()
    {
        $events[] = DomainEvent::create('SomethingHappened', []);
        $events[] = DomainEvent::create('SomethingElseHappened', []);

        $store = $this->createEventStore(new InMemoryEventBus());

        $store->saveEvents($events[0]->getUuid(), $events, EventStoreInterface::VERSION_NEW);
    }

    public function handle(EventInterface $event)
    {
        $this->events[] = $event;
    }
}
