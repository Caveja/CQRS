<?php
namespace Caveja\CQRS\Event\Store;

use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Exception\ConcurrencyException;
use ValueObjects\Identity\UUID;

/**
 * Interface EventStoreInterface
 * @package Caveja\CQRS\Event\Store
 */
interface EventStoreInterface
{
    /**
     * First element
     */
    const VERSION_FIRST = 0;

    /**
     * Any version
     */
    const VERSION_ANY = -2;

    /**
     * Expects that aggregate does not exists and will be created
     */
    const VERSION_NEW = -1;

    /**
     * @param  UUID                 $aggregateId
     * @param  array                $events
     * @param  int                  $expectedVersion
     * @throws ConcurrencyException on wrong $expectedVersion
     */
    public function saveEvents(UUID $aggregateId, array $events, $expectedVersion = self::VERSION_ANY);

    /**
     * @param  UUID           $aggregateId
     * @return EventInterface
     */
    public function last(UUID $aggregateId);

    /**
     * @param  UUID $aggregateId
     * @return int
     */
    public function count(UUID $aggregateId);

    /**
     * @param  UUID      $aggregateId
     * @return \Iterator
     */
    public function getEventsForAggregate(UUID $aggregateId);
}
