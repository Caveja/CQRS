<?php
namespace Caveja\CQRS\Repository;

use Caveja\CQRS\Event\Store\EventStoreInterface;
use Caveja\CQRS\Exception\AggregateNotFoundException;
use Caveja\CQRS\Model\AggregateRoot;
use ValueObjects\Identity\UUID;

/**
 * Class Repository
 * @package Caveja\Domain\Repository
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * @var \Caveja\CQRS\Event\Store\EventStoreInterface
     */
    protected $eventStore;

    /**
     * @param \Caveja\CQRS\Event\Store\EventStoreInterface $eventStore
     */
    public function __construct(EventStoreInterface $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param AggregateRoot $aggregate
     * @param int           $expectedVersion
     */
    public function save(AggregateRoot $aggregate, $expectedVersion)
    {
        $this
            ->eventStore
            ->saveEvents(
                $aggregate->getId(),
                $aggregate->getUncommittedChanges(),
                $expectedVersion
            )
        ;

        $aggregate->markChangesAsCommitted();
    }

    /**
     * @param  UUID                                              $id
     * @return AggregateRoot
     * @throws \Caveja\CQRS\Exception\AggregateNotFoundException
     */
    public function getById(UUID $id)
    {
        $events = $this
            ->eventStore
            ->getEventsForAggregate($id)
        ;

        if (count($events) === 0) {
            throw new AggregateNotFoundException(sprintf('No events for Aggregate ID %s', $id));
        }

        $weightHistory = $this->create();
        $weightHistory->loadFromHistory($events);

        return $weightHistory;
    }

    /**
     * @return AggregateRoot
     */
    abstract protected function create();
}
