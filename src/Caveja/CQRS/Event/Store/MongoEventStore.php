<?php

namespace Caveja\CQRS\Event\Store;

use Caveja\CQRS\Event\EventInterface;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Database;
use Doctrine\MongoDB\Exception\ResultException;
use Caveja\CQRS\Exception\AggregateNotFoundException;
use Caveja\CQRS\Exception\ConcurrencyException;
use Caveja\CQRS\Event\Bus\EventPublisherInterface;
use Caveja\CQRS\Event\DomainEvent;
use ValueObjects\Identity\UUID;

/**
 * Class MongoEventStore
 * @package Caveja\CQRS\Event\Store
 */
class MongoEventStore implements EventStoreInterface
{
    /**
     * @var Collection
     */
    private $events;

    /**
     * @var Collection
     */
    private $counters;

    /**
     * @var \Caveja\CQRS\Event\Bus\EventPublisherInterface
     */
    private $eventPublisher;

    /**
     * @param Database                                       $database
     * @param \Caveja\CQRS\Event\Bus\EventPublisherInterface $eventPublisher
     */
    public function __construct(Database $database, EventPublisherInterface $eventPublisher)
    {
        $this->events = $database->selectCollection('events');
        $this->counters = $database->selectCollection('counters');

        $this->events->ensureIndex(['aggregate_id' => 1, 'version' => 1], ['unique' => true]);
        $this->events->ensureIndex(['type' => 1]);

        $this->eventPublisher = $eventPublisher;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventsForAggregate(UUID $aggregateId)
    {
        $cursor = $this
            ->events
            ->find([
                'aggregate_id' => $aggregateId->getValue()
            ])
            ->sort([
                'version' => 1
            ])
        ;

        $this->ensureAggregateFound($aggregateId, $cursor);

        return new MongoEventIterator($cursor);
    }

    /**
     * {@inheritdoc}
     */
    public function saveEvents(UUID $aggregateId, array $events, $expectedVersion = self::VERSION_ANY)
    {
        foreach ($events as $event) {
            $data = [
                '_id' => $event->getUuid()->getValue(),
                'aggregate_id' => $aggregateId->getValue(),
                'type' => $event->getType(),
                'data' => $event->getData(),
                'version' => $this->sequence($aggregateId, $expectedVersion),
            ];

            $this->events->insert($data);
            $this->eventPublisher->publish($event);
        }
    }

    /**
     * @param  UUID $aggregateId
     * @return int
     */
    public function count(UUID $aggregateId)
    {
        return $this
            ->events
            ->count([
                'aggregate_id' => $aggregateId->getValue()
            ])
        ;
    }

    /**
     * @param  UUID                       $aggregateId
     * @return EventInterface
     * @throws AggregateNotFoundException
     */
    public function last(UUID $aggregateId)
    {
        $cursor = $this
            ->events
            ->find([
                'aggregate_id' => $aggregateId->getValue()
            ])
            ->sort([
                'version' => -1
            ])
            ->limit(1)
        ;

        $this->ensureAggregateFound($aggregateId, $cursor);

        return DomainEvent::fromArray($cursor->getNext());
    }

    private function sequence(UUID $aggregateId, $expectedVersion = self::VERSION_ANY)
    {
        if ($expectedVersion === self::VERSION_NEW) {
            return $this->createNewSequence($aggregateId, $expectedVersion);
        }

        $query = [
            '_id' => $aggregateId->getValue(),
        ];

        if ($expectedVersion !== self::VERSION_ANY) {
            $query['value'] = $expectedVersion;
        }

        try {
            return $this->counters->findAndUpdate($query,[
                '$inc' => ['value' => 1]
            ],[
                'upsert' => true,
                'new' => $expectedVersion === self::VERSION_ANY,
            ])['value'] - 1;
        } catch (ResultException $e) {
            throw new ConcurrencyException(sprintf('$expectedVersion = %d not matching', $expectedVersion, 0, $e));
        }
    }

    /**
     * @param  UUID                       $aggregateId
     * @throws AggregateNotFoundException
     */
    private function throwAggregateNotFoundException(UUID $aggregateId)
    {
        throw new AggregateNotFoundException(sprintf('Aggregate with id %s not found', $aggregateId->getValue()));
    }

    /**
     * @param UUID $aggregateId
     * @param $cursor
     */
    private function ensureAggregateFound(UUID $aggregateId, $cursor)
    {
        if ($cursor->count(true) < 1) {
            $this->throwAggregateNotFoundException($aggregateId);
        }
    }

    /**
     * @param  UUID                 $aggregateId
     * @param  int                  $expectedVersion
     * @return 0
     * @throws ConcurrencyException
     */
    private function createNewSequence(UUID $aggregateId, $expectedVersion)
    {
        $data = [
            '_id' => $aggregateId->getValue(),
            'value' => 0,
        ];

        try {
            $this
                ->counters
                ->insert($data)
            ;
        } catch (\MongoCursorException $e) {
            throw new ConcurrencyException(sprintf('$expectedVersion = %d not matching', $expectedVersion, 0, $e));
        }

        return $data['value'];
    }
}
