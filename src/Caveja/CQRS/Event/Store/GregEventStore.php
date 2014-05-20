<?php
namespace Caveja\CQRS\Event\Store;

use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Exception\ConcurrencyException;
use EventStore\ConnectionInterface;
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
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param  UUID                 $aggregateId
     * @param  EventInterface[]     $events
     * @param  int                  $expectedVersion
     * @throws ConcurrencyException on wrong $expectedVersion
     */
    public function saveEvents(UUID $aggregateId, array $events, $expectedVersion = self::VERSION_ANY)
    {
        // TODO: Implement saveEvents() method.
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
        // TODO: Implement count() method.
    }

    /**
     * @param  UUID      $aggregateId
     * @return \Iterator
     */
    public function getEventsForAggregate(UUID $aggregateId)
    {
        // TODO: Implement getEventsForAggregate() method.
    }
}
