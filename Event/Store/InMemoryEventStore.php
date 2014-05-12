<?php
namespace Caveja\CQRS\Event\Store;

use ArrayIterator;
use Caveja\CQRS\Event\EventInterface;
use Caveja\CQRS\Exception\AggregateNotFoundException;
use Caveja\CQRS\Exception\ConcurrencyException;
use Foodlogger\Domain\Event\Bus\EventPublisherInterface;
use ValueObjects\Identity\UUID;

/**
 * Class InMemoryEventStore
 * @package Caveja\CQRS\Event\Store
 */
class InMemoryEventStore implements EventStoreInterface
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * @var array
     */
    private $last = [];

    /**
     * @var EventPublisherInterface
     */
    private $eventPublisher;

    /**
     * @param EventPublisherInterface $eventPublisher
     */
    public function __construct(EventPublisherInterface $eventPublisher)
    {
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @param  UUID                 $aggregateId
     * @param  EventInterface[]     $events
     * @param  int                  $expectedVersion
     * @throws ConcurrencyException
     */
    public function saveEvents(UUID $aggregateId, array $events, $expectedVersion = self::VERSION_ANY)
    {
        $currentVersion = $this->getVersion($aggregateId);

        if ($expectedVersion !== self::VERSION_ANY && $currentVersion !== $expectedVersion) {
            throw new ConcurrencyException(sprintf('Expected version %d, got %d', $expectedVersion, $currentVersion));
        }

        $id = $aggregateId->getValue();

        foreach ($events as $event) {
            $event->setVersion(++$currentVersion);
            $this->last[$id] = $event;
            $this->events[$id][] = $event;

            $this->eventPublisher->publish($event);
        }
    }

    /**
     * @param  UUID $aggregateId
     * @return int
     */
    public function count(UUID $aggregateId)
    {
        $id = $aggregateId->getValue();

        if (isset($this->events[$id])) {
            return count($this->events[$id]);
        }

        return 0;
    }

    /**
     * @param  UUID                       $aggregateId
     * @return EventInterface
     * @throws AggregateNotFoundException
     */
    public function last(UUID $aggregateId)
    {
        return $this->ensureAggregateFound($aggregateId, $this->last);
    }

    /**
     * @param  UUID          $aggregateId
     * @return ArrayIterator
     */
    public function getEventsForAggregate(UUID $aggregateId)
    {
        return new ArrayIterator($this->ensureAggregateFound($aggregateId, $this->events));
    }

    /**
     * @param  UUID                       $aggregateId
     * @param  array                      $array
     * @return mixed
     * @throws AggregateNotFoundException
     */
    private function ensureAggregateFound(UUID $aggregateId, array $array)
    {
        $id = $aggregateId->getValue();
        if (!isset($array[$id])) {
            throw new AggregateNotFoundException(sprintf('Aggregate with id %s not found', $aggregateId->getValue()));
        }

        return $array[$id];
    }

    /**
     * @param  UUID $aggregateId
     * @return int
     */
    private function getVersion(UUID $aggregateId)
    {
        if (isset($this->events[$aggregateId->getValue()])) {
            return count($this->events[$aggregateId->getValue()]) - 1;
        }

        return self::VERSION_NEW;
    }
}
