<?php
namespace Caveja\CQRS\Tests\Repository;

use Caveja\CQRS\Event\Bus\InMemoryEventBus;
use Caveja\CQRS\Event\Store\EventStoreInterface;
use Caveja\CQRS\Event\Store\InMemoryEventStore;
use Caveja\CQRS\Model\AggregateRoot;
use Caveja\CQRS\Repository\Repository;
use PHPUnit_Framework_TestCase as TestCase;
use ValueObjects\Identity\UUID;

abstract class RepositoryTest extends TestCase
{
    /**
     * @var EventStoreInterface
     */
    protected $eventStore;

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @return AggregateRoot
     */
    abstract protected function createAggregate();

    /**
     * @param  EventStoreInterface $eventStore
     * @return Repository
     */
    abstract protected function createRepository(EventStoreInterface $eventStore);

    protected function setUp()
    {
        $this->eventStore = new InMemoryEventStore(new InMemoryEventBus());
        $this->repository = $this->createRepository($this->eventStore);
    }

    /**
     * @expectedException \Caveja\CQRS\Exception\AggregateNotFoundException
     */
    public function testGetByIdNotFoundThrowsException()
    {
        $this->repository->getById(new UUID());
    }

    public function testSaveWillSaveEventsOnEventStore()
    {
        $aggregate = $this->createAggregate();

        $this->repository->save($aggregate, EventStoreInterface::VERSION_NEW);
        $this->assertGreaterThanOrEqual(1, $this->eventStore->count($aggregate->getId()), 'There should be at least one id');
    }

    public function testSaveWillCommitChanges()
    {
        $aggregate = $this->createAggregate();

        $this->repository->save($aggregate, EventStoreInterface::VERSION_ANY);

        $this->assertCount(0, $aggregate->getUncommittedChanges());
    }

    /**
     * @expectedException \Caveja\CQRS\Exception\ConcurrencyException
     */
    public function testOptimisticLockingExceptionIsBubbledUp()
    {
        $aggregate = $this->createAggregate();

        $this->repository->save($aggregate, 10);
    }
}
