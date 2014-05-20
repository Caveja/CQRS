<?php
namespace Caveja\CQRS\Event\Store;

use Caveja\CQRS\Event\Bus\EventPublisherInterface;
use Caveja\CQRS\Event\DomainEvent;
use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Exception\AggregateNotFoundException;
use Caveja\CQRS\Exception\ConcurrencyException;
use Doctrine\MongoDB\ArrayIterator;
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
        try {
            $this->connection->appendToStream($this->getStreamName($aggregateId), $expectedVersion, $this->convertEvents($events));
        } catch (\EventStore\Exception\ConcurrencyException $e) {
            throw new ConcurrencyException($e->getMessage(), $e->getCode(), $e);
        }

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
        return $this->getEventsForAggregate($aggregateId)->last();
    }

    /**
     * @param  UUID $aggregateId
     * @return int
     */
    public function count(UUID $aggregateId)
    {
        try {
            return count($this->getEventsForAggregate($aggregateId));
        } catch (AggregateNotFoundException $e) {
            return 0;
        }
    }

    /**
     * @param  UUID                       $aggregateId
     * @return ArrayIterator|\Iterator
     * @throws AggregateNotFoundException
     * @throws ConcurrencyException
     */
    public function getEventsForAggregate(UUID $aggregateId)
    {
        $events = [];

        $start = 0;
        do {
            try {
                $slice = $this->connection->readStreamEventsForward($this->getStreamName($aggregateId), $start, 20, false);
            } catch (\EventStore\Exception\ConcurrencyException $e) {
                throw new ConcurrencyException($e->getMessage(), $e->getCode(), $e);
            }

            if ($slice->getStatus() === 'StreamNotFound') {
                throw new AggregateNotFoundException();
            }

            $start = $slice->getNextEventNumber();
            $i = $slice->getFromEventNumber();

            foreach ($slice->getEvents() as $event) {

                $data = $event->getData();

                $id = $data['id'];
                unset($data['id']);

                $events[] = $event = new DomainEvent(new UUID($id), $event->getType(), $data);
                $event->setVersion($i++);
            }
        } while ($start !== null);

        return new ArrayIterator($events);
    }

    /**
     * @param  EventInterface[] $events
     * @return array
     */
    private function convertEvents(array $events)
    {
        $converted = [];

        foreach ($events as $event) {
            $data = $event->getData();
            $data['id'] = (string) $event->getUuid();
            $converted[] = new EventData($data['id'], $event->getType(), $data);
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
