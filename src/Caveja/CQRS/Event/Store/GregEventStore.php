<?php
namespace Caveja\CQRS\Event\Store;

use Caveja\CQRS\Event\Bus\EventPublisherInterface;
use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Exception\ConcurrencyException;
use EventStore\ConnectionInterface;
use EventStore\EventData;
use ValueObjects\Identity\UUID;

/**
 * Class GregEventStore
 * @package Caveja\CQRS\Event\Store
 */
class GregEventStore implements EventStoreInterface
{
    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var EventPublisherInterface
     */
    private $eventPublisher;

    /**
     * @param ConnectionInterface     $connection
     * @param EventPublisherInterface $eventPublisher
     */
    public function __construct(ConnectionInterface $connection, EventPublisherInterface $eventPublisher)
    {
        $this->connection = $connection;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param  UUID                 $aggregateId
     * @param  EventInterface[]     $events
     * @param  int                  $expectedVersion
     * @throws ConcurrencyException on wrong $expectedVersion
     */
    public function saveEvents(UUID $aggregateId, array $events, $expectedVersion = self::VERSION_ANY)
    {
        $this->connection->appendToStream($this->getStreamName($aggregateId), $expectedVersion, $this->convertEvents($events));

        foreach ($events as $event) {
            $this->eventPublisher->publish($event);
        }
    }

    /**
     * @param  UUID           $aggregateId
     * @return EventInterface
     */
    public function last(UUID $aggregateId)
    {
        // TODO: Implement last() method.
    }

    /**
     * @param  UUID $aggregateId
     * @return int
     */
    public function count(UUID $aggregateId)
    {
        $slice = $this->connection->readStreamEventsForward($this->getStreamName($aggregateId), 0, 100, false);

        return $slice->getNextEventNumber() - 1;
    }

    /**
     * @param  UUID      $aggregateId
     * @return \Iterator
     */
    public function getEventsForAggregate(UUID $aggregateId)
    {
        // TODO: Implement getEventsForAggregate() method.
    }

    /**
     * @param  EventInterface[] $events
     * @return array
     */
    private function convertEvents(array $events)
    {
        $converted = [];

        foreach ($events as $event) {
            $converted[] = new EventData((string) $event->getUuid(), $event->getType(), $event->getData());
        }

        return $converted;
    }

    /**
     * @param  UUID   $aggregateId
     * @return string
     */
    protected function getStreamName(UUID $aggregateId)
    {
        return 'aggregate-' . $aggregateId;
    }
}
